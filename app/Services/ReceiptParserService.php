<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class ReceiptParserService
{
    /**
     * Parse single receipt image using 9router AI Vision API.
     *
     * @return array<string, mixed>
     */
    public function parseImage(string $filePath, string $priceType = 'unit_price'): array
    {
        return $this->parseSingleImage($filePath, $priceType);
    }

    /**
     * Parse multiple receipt images (overlapping screenshots) safely by batching calls to avoid API timeouts.
     *
     * @param  array<int, string>  $filePaths
     * @return array<string, mixed>
     */
    public function parseImages(array $filePaths, string $priceType = 'unit_price'): array
    {
        $validPaths = array_filter($filePaths, 'file_exists');
        if (empty($validPaths)) {
            throw new InvalidArgumentException('Tidak ada file gambar struk yang valid di server.');
        }

        // If only 1 image, process directly
        if (count($validPaths) === 1) {
            return $this->parseSingleImage(reset($validPaths), $priceType);
        }

        $allRawItems = [];
        $merchantName = 'Toko / Restoran';
        $maxDeliveryFee = 0.0;
        $maxServiceFee = 0.0;
        $maxDiscount = 0.0;
        $maxTotal = 0.0;
        $successfulParses = 0;
        $errors = [];

        foreach ($validPaths as $path) {
            $parsed = $this->parseSingleImage($path, $priceType);

            if ($parsed['success']) {
                $successfulParses++;
                if (! empty($parsed['merchant_name']) && $parsed['merchant_name'] !== 'Toko / Restoran') {
                    $merchantName = $parsed['merchant_name'];
                }
                foreach ($parsed['items'] as $item) {
                    $allRawItems[] = $item;
                }

                $maxDeliveryFee = max($maxDeliveryFee, (float) $parsed['delivery_fee']);
                $maxServiceFee = max($maxServiceFee, (float) $parsed['service_fee']);
                $maxDiscount = max($maxDiscount, (float) $parsed['discount']);
                $maxTotal = max($maxTotal, (float) $parsed['total']);
            } else {
                $errors[] = $parsed['error'] ?? 'Unknown error';
            }
        }

        if ($successfulParses === 0) {
            return $this->emptyFallback('Gagal memproses gambar-gambar struk: '.implode(' | ', $errors));
        }

        // Deduplicate items extracted across multiple screenshot images
        $deduplicatedItems = $this->deduplicateItems($allRawItems);

        // Perform Price Format Enforcing
        $processed = $this->applyPriceFormatRules($deduplicatedItems, $priceType, $maxDeliveryFee, $maxServiceFee, $maxDiscount, $maxTotal);

        return [
            'success' => true,
            'merchant_name' => $merchantName,
            'items' => $processed['items'],
            'delivery_fee' => $maxDeliveryFee,
            'service_fee' => $maxServiceFee,
            'discount' => $maxDiscount,
            'total' => $maxTotal,
            'auto_corrected' => $processed['auto_corrected'],
            'auto_corrected_message' => $processed['auto_corrected_message'],
        ];
    }

    /**
     * Internal method to parse a single receipt image with user-selected price format mode.
     *
     * @return array<string, mixed>
     */
    protected function parseSingleImage(string $filePath, string $priceType = 'unit_price'): array
    {
        $apiKey = (string) config('services.ninerouter.api_key', '');
        $baseUrl = (string) config('services.ninerouter.api_base', 'https://api.9router.com/v1');
        $model = (string) config('services.ninerouter.model', 'gemma4-31b');

        if (! file_exists($filePath)) {
            return $this->emptyFallback('File struk tidak ditemukan di server: '.$filePath);
        }

        $imageBytes = file_get_contents($filePath);
        if ($imageBytes === false) {
            return $this->emptyFallback('Gagal membaca file gambar struk: '.$filePath);
        }

        $mimeType = mime_content_type($filePath) ?: 'image/jpeg';
        $base64Image = 'data:'.$mimeType.';base64,'.base64_encode($imageBytes);

        if ($priceType === 'total_price') {
            $priceInstruction = 'PETUNJUK USER (PENTING): Pengguna mengonfirmasi bahwa nominal yang tertera pada kolom harga di struk adalah TOTAL HARGA BARIS / TOTAL KUANTITAS (Subtotal untuk `qty` barang tersebut). Kamu HARUS MEMBAGI nominal tersebut dengan `qty` (yaitu price_satuan = nominal / qty) agar field `price` pada JSON berisi HARGA SATUAN per 1 pcs.';
        } else {
            $priceInstruction = 'PETUNJUK USER (PENTING - DEFAULT): Pengguna mengonfirmasi bahwa nominal yang tertera pada kolom harga di struk adalah HARGA SATUAN (Unit Price per 1 pcs). Ambil angka tersebut langsung tanpa membaginya dengan `qty` sebagai field `price`.';
        }

        $prompt = <<<PROMPT
Anda adalah asisten AI ekstraksi struk/nota belanja di Indonesia (ShopeeFood, GoFood, GrabFood, Restoran, Supermarket, dll).
Tugas Anda adalah membaca gambar struk ini dan mengembalikan JSON HANYA dengan struktur berikut tanpa teks pembuka atau penutup:

{
  "merchant_name": "Nama Toko / Restoran",
  "items": [
    {
      "name": "Nama Item 1",
      "qty": 1,
      "price": 25000
    }
  ],
  "delivery_fee": 10000,
  "service_fee": 3000,
  "discount": 5000,
  "total": 33000
}

ATURAN DEDUKSI HARGA:
{$priceInstruction}

Aturan Tambahan:
1. Field `price` adalah HARGA SATUAN (Unit Price per 1 pcs) angka bulat tanpa desimal atau titik/koma dalam Rupiah.
2. `delivery_fee` adalah biaya ongkos kirim (0 jika tidak ada).
3. `service_fee` adalah total biaya layanan, biaya penanganan, biaya aplikasi, atau pembulatan.
4. `discount` adalah total potongan harga / promo / voucher (0 jika tidak ada).
5. Jangan sertakan item promo/diskon ke dalam list `items`, diskon masukkan ke field `discount`.
6. Kembalikan format JSON murni tanpa markdown.
7. PENTING: Sertakan seluruh varian rasa, tingkat gula (sugar level), tingkat es (less ice / normal ice), topping, ukuran, atau opsi catatan pesanan langsung ke dalam field `name` (contoh: 'Kopi Susu (Less Ice)', 'Kopi Susu (Normal Ice)', 'Ayam Geprek (Level 3)'). Jangan menghilangkan varian karena varian berbeda merupakan item pesanan terpisah.
8. PENTING: Jika gambar struk terpotong/hanya menampilkan bagian atas atau tengah tanpa rincian total pembayaran di bagian bawah struk, isi field `total`: 0, `delivery_fee`: 0, `service_fee`: 0, `discount`: 0 (JANGAN menebak atau menghitung total sendiri jika baris Total tidak tercantum pada gambar).
PROMPT;

        try {
            $endpoint = rtrim($baseUrl, '/').'/chat/completions';

            $payload = [
                'model' => $model,
                'stream' => false,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $prompt],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => $base64Image,
                                ],
                            ],
                        ],
                    ],
                ],
                'temperature' => 0.1,
            ];

            Log::info("Sending single receipt parsing request ({$priceType}) to 9router endpoint: {$endpoint} using model: {$model}");

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(45)->post($endpoint, $payload);

            $responseBody = $response->body();

            if ($response->failed()) {
                Log::error('9router API request failed with status: '.$response->status().' Body: '.$responseBody);

                return $this->emptyFallback("Gagal menghubungi AI Server 9router (Status: {$response->status()}). Response: ".mb_substr($responseBody, 0, 200));
            }

            $rawText = '';

            if (str_contains($responseBody, 'data:') || str_contains($responseBody, 'chat.completion.chunk')) {
                $lines = explode("\n", $responseBody);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (str_starts_with($line, 'data:')) {
                        $jsonStr = trim(substr($line, 5));
                        if ($jsonStr === '[DONE]') {
                            break;
                        }
                        $chunk = json_decode($jsonStr, true);
                        if (isset($chunk['choices'][0]['delta']['content'])) {
                            $rawText .= $chunk['choices'][0]['delta']['content'];
                        } elseif (isset($chunk['choices'][0]['message']['content'])) {
                            $rawText .= $chunk['choices'][0]['message']['content'];
                        } elseif (isset($chunk['choices'][0]['text'])) {
                            $rawText .= $chunk['choices'][0]['text'];
                        }
                    }
                }
            } else {
                $responseData = $response->json();
                if (isset($responseData['choices'][0]['message']['content'])) {
                    $contentObj = $responseData['choices'][0]['message']['content'];
                    if (is_string($contentObj)) {
                        $rawText = $contentObj;
                    } elseif (is_array($contentObj)) {
                        foreach ($contentObj as $part) {
                            if (is_array($part) && isset($part['text'])) {
                                $rawText .= $part['text']."\n";
                            } elseif (is_string($part)) {
                                $rawText .= $part."\n";
                            }
                        }
                    }
                } elseif (isset($responseData['choices'][0]['text'])) {
                    $rawText = $responseData['choices'][0]['text'];
                }
            }

            if (empty(trim($rawText))) {
                return $this->emptyFallback('AI mengembalikan respon kosong.');
            }

            $parsed = null;
            if (preg_match('/\{[\s\S]*\}/', $rawText, $matches)) {
                $jsonCandidate = $matches[0];
                $parsed = json_decode($jsonCandidate, true);
            }

            if (! is_array($parsed)) {
                return $this->emptyFallback('Respon AI tidak berformat JSON yang valid.');
            }

            $rawItems = array_map(function ($item) {
                return [
                    'name' => (string) ($item['name'] ?? 'Item'),
                    'qty' => (int) max(1, $item['qty'] ?? 1),
                    'price' => (float) max(0, $item['price'] ?? 0),
                ];
            }, $parsed['items'] ?? []);

            $deliveryFee = (float) max(0, $parsed['delivery_fee'] ?? 0);
            $serviceFee = (float) max(0, $parsed['service_fee'] ?? 0);
            $discount = (float) max(0, $parsed['discount'] ?? 0);
            $total = (float) max(0, $parsed['total'] ?? 0);

            $processed = $this->applyPriceFormatRules($rawItems, $priceType, $deliveryFee, $serviceFee, $discount, $total);

            return [
                'success' => true,
                'merchant_name' => $parsed['merchant_name'] ?? 'Toko / Restoran',
                'items' => $processed['items'],
                'delivery_fee' => $deliveryFee,
                'service_fee' => $serviceFee,
                'discount' => $discount,
                'total' => $total,
                'auto_corrected' => $processed['auto_corrected'],
                'auto_corrected_message' => $processed['auto_corrected_message'],
            ];

        } catch (Throwable $e) {
            Log::error('Exception during receipt parsing: '.$e->getMessage());

            return $this->emptyFallback('Error: '.$e->getMessage());
        }
    }

    /**
     * Apply user-defined price format rules.
     *
     * @param  array<int, array{name: string, qty: int, price: float}>  $items
     * @return array{items: array<int, array{name: string, qty: int, price: float}>, auto_corrected: bool, auto_corrected_message: string}
     */
    protected function applyPriceFormatRules(array $items, string $priceType, float $deliveryFee, float $serviceFee, float $discount, float $total): array
    {
        if (empty($items)) {
            return [
                'items' => [],
                'auto_corrected' => false,
                'auto_corrected_message' => '',
            ];
        }

        if ($priceType === 'total_price') {
            $corrected = [];
            $wasAdjusted = false;
            foreach ($items as $item) {
                if ($item['qty'] > 1) {
                    $unitPrice = round($item['price'] / $item['qty']);
                    $corrected[] = array_merge($item, ['price' => $unitPrice]);
                    $wasAdjusted = true;
                } else {
                    $corrected[] = $item;
                }
            }

            return [
                'items' => $corrected,
                'auto_corrected' => $wasAdjusted,
                'auto_corrected_message' => 'Format diterapkan (Harga Struk = Harga Total Item): Nominal baris item secara otomatis dibagi dengan jumlah kuantitas untuk menghasilkan harga satuan.',
            ];
        }

        return [
            'items' => $items,
            'auto_corrected' => false,
            'auto_corrected_message' => '',
        ];
    }

    /**
     * Deduplicate items extracted across multiple screenshot images.
     *
     * @param  array<int, array{name: string, qty: int, price: float}>  $items
     * @return array<int, array{name: string, qty: int, price: float}>
     */
    protected function deduplicateItems(array $items): array
    {
        $unique = [];
        foreach ($items as $item) {
            $rawName = (string) ($item['name'] ?? '');
            $cleanName = trim((string) preg_replace('/\s+/', ' ', $rawName));
            if ($cleanName === '') {
                continue;
            }

            $price = (float) ($item['price'] ?? 0);
            $normalizedKey = $this->normalizeItemKey($cleanName, $price);

            if (isset($unique[$normalizedKey])) {
                $unique[$normalizedKey]['qty'] = max((int) $unique[$normalizedKey]['qty'], (int) ($item['qty'] ?? 1));
                if (mb_strlen($cleanName) > mb_strlen($unique[$normalizedKey]['name'])) {
                    $unique[$normalizedKey]['name'] = $cleanName;
                }
            } else {
                $unique[$normalizedKey] = [
                    'name' => $cleanName,
                    'qty' => max(1, (int) ($item['qty'] ?? 1)),
                    'price' => $price,
                ];
            }
        }

        return array_values($unique);
    }

    /**
     * Standardize comparison key for item deduplication.
     */
    protected function normalizeItemKey(string $name, float $price): string
    {
        $str = mb_strtolower($name, 'UTF-8');
        $str = (string) preg_replace('/\b(catatan|notes?|opsi)\s*:\s*/iu', ' ', $str);
        $str = (string) preg_replace('/(\d+)\s*(gram|gr)\b/iu', '${1}g', $str);
        $str = (string) preg_replace('/(\d+)\s*pcs\b/iu', '${1}pcs', $str);
        $str = (string) preg_replace('/(\d+)\s*ml\b/iu', '${1}ml', $str);
        $str = (string) preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $str);
        $str = trim((string) preg_replace('/\s+/', ' ', $str));

        return $str.'___'.(int) round($price);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyFallback(string $message): array
    {
        return [
            'success' => false,
            'error' => $message,
            'merchant_name' => '',
            'items' => [],
            'delivery_fee' => 0,
            'service_fee' => 0,
            'discount' => 0,
            'total' => 0,
            'auto_corrected' => false,
            'auto_corrected_message' => '',
        ];
    }
}
