<?php

namespace App\Services;

class QrisService
{
    /**
     * Parse raw EMVCo payload into Tag-Length-Value (TLV) key-value map.
     *
     * @return array<string, string>
     */
    public function parseEmvcoTlv(string $payload): array
    {
        $tags = [];
        $offset = 0;
        $len = strlen($payload);

        while ($offset + 4 <= $len) {
            $tag = substr($payload, $offset, 2);
            $valLenStr = substr($payload, $offset + 2, 2);

            if (! ctype_digit($valLenStr)) {
                break;
            }

            $valLen = (int) $valLenStr;
            $offset += 4;

            if ($offset + $valLen > $len) {
                $tags[$tag] = substr($payload, $offset);
                break;
            }

            $tags[$tag] = substr($payload, $offset, $valLen);
            $offset += $valLen;
        }

        return $tags;
    }

    /**
     * Validate if string is a standard EMVCo QRIS payload.
     */
    public function isValidQris(string $qrisCode): bool
    {
        $code = trim($qrisCode);
        if (strlen($code) < 30) {
            return false;
        }

        // Must start with 000201 (Payload format indicator)
        if (! str_starts_with($code, '000201')) {
            return false;
        }

        $tlv = $this->parseEmvcoTlv($code);

        // Must have format indicator (Tag 00)
        if (! isset($tlv['00']) || $tlv['00'] !== '01') {
            return false;
        }

        // Must contain Indonesian country code tag (5802ID) or IDR currency tag (5303360)
        $hasCountryOrCurrency = (isset($tlv['58']) && $tlv['58'] === 'ID') || (isset($tlv['53']) && $tlv['53'] === '360');
        if (! $hasCountryOrCurrency && ! str_contains($code, '5802ID') && ! str_contains($code, '5303360')) {
            return false;
        }

        return true;
    }

    /**
     * Extract Merchant Info from raw static EMVCo QRIS string.
     *
     * @return array{merchant_name: string, merchant_city: string}
     */
    public function extractMerchantInfo(string $qrisCode): array
    {
        $merchantName = 'Merchant QRIS';
        $merchantCity = 'Indonesia';
        $code = trim($qrisCode);

        // 1. Primary: Parse sequential TLV tags immediately following the mandatory 5802ID anchor
        $pos58 = strpos($code, '5802ID');
        if ($pos58 !== false) {
            $afterCountry = substr($code, $pos58 + 6);
            $offset = 0;
            $afterLen = strlen($afterCountry);

            while ($offset + 4 <= $afterLen) {
                $tag = substr($afterCountry, $offset, 2);
                $valLenStr = substr($afterCountry, $offset + 2, 2);

                if (! ctype_digit($valLenStr)) {
                    break;
                }

                $valLen = (int) $valLenStr;
                $offset += 4;
                $val = substr($afterCountry, $offset, $valLen);
                $offset += $valLen;

                if ($tag === '59') {
                    $merchantName = $val;
                } elseif ($tag === '60') {
                    $merchantCity = $val;
                } elseif ($tag === '63') {
                    break;
                }
            }
        }

        // 2. Secondary fallback: Full root TLV parse if anchor parsing didn't find either
        if ($merchantName === 'Merchant QRIS' || $merchantCity === 'Indonesia') {
            $tlv = $this->parseEmvcoTlv($code);
            if (! empty($tlv['59']) && $merchantName === 'Merchant QRIS') {
                $merchantName = $tlv['59'];
            }
            if (! empty($tlv['60']) && $merchantCity === 'Indonesia') {
                $merchantCity = $tlv['60'];
            }
        }

        return [
            'merchant_name' => trim($merchantName),
            'merchant_city' => trim($merchantCity),
        ];
    }

    /**
     * Convert Static QRIS code to Dynamic QRIS code with specified nominal amount.
     */
    public function convertToDynamic(string $qrisCode, float|int|string $amount): string
    {
        // Format integer nominal (no decimals for IDR)
        $nominalStr = (string) (int) round((float) $amount);

        // Remove trailing 4-char CRC if present
        $cleanQris = substr($qrisCode, 0, -4);
        // Replace Tag 010211 (Static) with 010212 (Dynamic)
        $cleanQris = str_replace('010211', '010212', $cleanQris);

        // Split by 5802ID (Country Code tag)
        $parts = explode('5802ID', $cleanQris, 2);
        if (count($parts) < 2) {
            return $qrisCode; // Fallback if format is non-standard
        }

        $prefix = $parts[0];
        $suffix = $parts[1];

        // Tag 54: Transaction Amount
        $lenStr = str_pad((string) strlen($nominalStr), 2, '0', STR_PAD_LEFT);
        $nominalData = '54'.$lenStr.$nominalStr;

        $payloadToCrc = $prefix.$nominalData.'5802ID'.$suffix;
        $crc = $this->calculateCrc16($payloadToCrc);

        return $payloadToCrc.$crc;
    }

    /**
     * CRC16-CCITT (0xFFFF init, 0x1021 poly) calculation for EMVCo QRIS.
     */
    public function calculateCrc16(string $str): string
    {
        $crc = 0xFFFF;
        $len = strlen($str);

        for ($c = 0; $c < $len; $c++) {
            $crc ^= (ord($str[$c]) << 8);
            for ($i = 0; $i < 8; $i++) {
                if (($crc & 0x8000) !== 0) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc & 0xFFFF), 4, '0', STR_PAD_LEFT));
    }
}
