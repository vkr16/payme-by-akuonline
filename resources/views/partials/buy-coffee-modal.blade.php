<!-- ==========================================
     MODAL: TRAKTIR KOPI MAS DEV / BUY ME COFFEE
     ========================================== -->
<div id="buyCoffeeModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden" role="dialog" aria-modal="true" aria-labelledby="buyCoffeeModalTitle">
    <div class="bg-white rounded-3xl p-5 sm:p-6 max-w-sm w-full text-center space-y-4 shadow-2xl border border-zinc-200 relative animate-in zoom-in-95 duration-200 max-h-[95vh] overflow-y-auto">
        <!-- Close Button -->
        <button type="button" onclick="window.closeBuyCoffeeModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full flex items-center justify-center text-zinc-400 hover:text-zinc-700 hover:bg-zinc-100 transition-colors cursor-pointer" aria-label="Tutup modal">
            <i class="fa-light fa-xmark text-sm"></i>
        </button>

        <!-- Header -->
        <div class="space-y-1.5 pt-1">
            <div class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 shadow-2xs mb-0.5">
                <i class="fa-light fa-mug-hot text-xl"></i>
            </div>
            <h3 id="buyCoffeeModalTitle" class="text-base font-bold text-zinc-900 mt-2">
                Traktir Kopi Mas Dev
            </h3>
            <p class="text-xs text-zinc-500 leading-relaxed px-2">
                Dukungan sukarela agar aplikasi <strong>PayMe</strong> tetap aktif, bebas iklan, dan terus dikembangkan.
            </p>
        </div>

        <!-- QRIS Card Area -->
        <div id="coffeeQrisCardArea" class="p-3.5 sm:p-4 rounded-2xl bg-zinc-50 border border-zinc-200 space-y-3 text-center relative overflow-hidden">
            <!-- Merchant Detail -->
            <div class="space-y-0.5 border-b border-zinc-200/60 pb-2.5">
                <div class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider">QRIS</div>
                <div class="text-sm font-black text-zinc-900 leading-tight">
                    AkuOnline IT Services
                </div>
                <div class="text-[11px] text-zinc-500 font-medium">
                    KOTA TANGERANG
                </div>
            </div>

            <!-- QR Code Display -->
            <div class="p-3 rounded-2xl bg-white border border-zinc-200 inline-block shadow-2xs">
                <img src="{{ asset('images/qris-developer.svg') }}" id="coffeeQrisSvgImg" alt="QRIS Developer AkuOnline IT Services" class="w-48 h-48 sm:w-52 sm:h-52 mx-auto object-contain block" crossOrigin="anonymous">
            </div>

            <!-- Nominal Badge & Info -->
            <div class="pt-0.5 space-y-1">
                <div class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-800 bg-emerald-100/70 px-2.5 py-0.5 rounded-full border border-emerald-200/70">
                    <i class="fa-light fa-sparkles text-[10px]"></i>
                    Nominal Bebas
                </div>
                <p class="text-[11px] text-zinc-500 leading-relaxed">
                    Scan via BCA, Mandiri, BRI, BNI, GoPay, OVO, ShopeePay, DANA, dll.
                </p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-2 pt-1">
            <button type="button" id="btnDownloadCoffeeCard" class="touch-target w-full py-2.5 px-4 rounded-xl bg-emerald-800 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-2xs transition-all cursor-pointer">
                <i class="fa-light fa-download text-slate-50"></i>
                <span>Unduh QRIS Donasi</span>
            </button>
            <button type="button" onclick="window.closeBuyCoffeeModal()" class="touch-target w-full py-2 px-4 rounded-xl text-xs font-semibold text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 transition-colors cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('buyCoffeeModal');
        const btnDownload = document.getElementById('btnDownloadCoffeeCard');

        window.openBuyCoffeeModal = function () {
            if (!modal) return;
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        };

        window.closeBuyCoffeeModal = function () {
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        // Close on backdrop click
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    window.closeBuyCoffeeModal();
                }
            });
        }

        // Close on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                window.closeBuyCoffeeModal();
            }
        });

        // Bind all links with href="#traktir-kopi" or [data-open-buy-coffee]
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('a[href="#traktir-kopi"], [data-open-buy-coffee]').forEach(function (el) {
                el.addEventListener('click', function (e) {
                    e.preventDefault();
                    window.openBuyCoffeeModal();
                });
            });

            // If page loaded with hash #traktir-kopi
            if (window.location.hash === '#traktir-kopi') {
                window.openBuyCoffeeModal();
            }
        });

        // Download Voucher Card PNG via Canvas
        if (btnDownload) {
            btnDownload.addEventListener('click', function () {
                const img = document.getElementById('coffeeQrisSvgImg');
                if (!img) return;

                const downloadCard = function (sourceImg) {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const scale = 3;
                    const cardWidth = 380 * scale;
                    const cardHeight = 520 * scale;

                    canvas.width = cardWidth;
                    canvas.height = cardHeight;

                    // Background
                    ctx.fillStyle = '#F4F4F5';
                    ctx.fillRect(0, 0, cardWidth, cardHeight);

                    // Top Banner
                    ctx.fillStyle = '#064E3B';
                    ctx.fillRect(0, 0, cardWidth, 68 * scale);

                    // Header Text
                    ctx.fillStyle = '#FFFFFF';
                    ctx.font = `bold ${14 * scale}px "Plus Jakarta Sans", sans-serif`;
                    ctx.textAlign = 'left';
                    ctx.fillText('PayMe • Traktir Kopi Mas Dev', 24 * scale, 32 * scale);

                    ctx.font = `${10 * scale}px "Plus Jakarta Sans", sans-serif`;
                    ctx.fillStyle = '#A7F3D0';
                    ctx.fillText('Dukung developer agar PayMe tetap gratis & terus dikembangkan', 24 * scale, 50 * scale);

                    // Destination Info
                    ctx.textAlign = 'center';
                    ctx.fillStyle = '#71717A';
                    ctx.font = `600 ${9 * scale}px "Plus Jakarta Sans", sans-serif`;
                    ctx.fillText('QRIS', cardWidth / 2, 95 * scale);

                    ctx.fillStyle = '#18181B';
                    ctx.font = `bold ${15 * scale}px "Plus Jakarta Sans", sans-serif`;
                    ctx.fillText('AkuOnline IT Services', cardWidth / 2, 115 * scale);

                    ctx.fillStyle = '#71717A';
                    ctx.font = `${10 * scale}px "Plus Jakarta Sans", sans-serif`;
                    ctx.fillText('KOTA TANGERANG', cardWidth / 2, 132 * scale);

                    // White QR Box
                    const qrBoxSize = 220 * scale;
                    const qrBoxX = (cardWidth - qrBoxSize) / 2;
                    const qrBoxY = 150 * scale;

                    ctx.fillStyle = '#FFFFFF';
                    ctx.beginPath();
                    ctx.roundRect(qrBoxX, qrBoxY, qrBoxSize, qrBoxSize, 16 * scale);
                    ctx.fill();

                    // Draw QR
                    const qrSize = 190 * scale;
                    const qrX = (cardWidth - qrSize) / 2;
                    const qrY = qrBoxY + (qrBoxSize - qrSize) / 2;
                    try {
                        ctx.drawImage(sourceImg, qrX, qrY, qrSize, qrSize);
                    } catch (err) {
                        console.error('Error drawing image to canvas:', err);
                    }

                    // Amount Section
                    const amountY = 398 * scale;
                    ctx.fillStyle = '#71717A';
                    ctx.font = `600 ${9 * scale}px "Plus Jakarta Sans", sans-serif`;
                    ctx.fillText('NOMINAL BEBAS', cardWidth / 2, amountY);

                    // ctx.fillStyle = '#064E3B';
                    // ctx.font = `900 ${20 * scale}px "Plus Jakarta Sans", sans-serif`;
                    // ctx.fillText('Bebas / Seikhlasnya', cardWidth / 2, amountY + (25 * scale));

                    // Footer
                    ctx.fillStyle = '#A1A1AA';
                    ctx.font = `${9 * scale}px "Plus Jakarta Sans", sans-serif`;
                    ctx.fillText('Scan via BCA, Mandiri, BRI, GoPay, OVO, ShopeePay, DANA dll.', cardWidth / 2, amountY + (48 * scale));
                    ctx.fillStyle = '#059669';
                    ctx.fillText('Terima kasih banyak atas apresiasi & dukunganmu! ❤️', cardWidth / 2, amountY + (63 * scale));

                    // Outer Border
                    ctx.strokeStyle = '#E4E4E7';
                    ctx.lineWidth = 1 * scale;
                    ctx.strokeRect(0, 0, cardWidth, cardHeight);

                    // Trigger Download
                    const link = document.createElement('a');
                    link.download = 'QRIS-Donasi-Developer-PayMe.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();

                    if (window.Notiflix) Notiflix.Notify.success('Card QRIS Donasi berhasil diunduh!');
                };

                if (img.complete && img.naturalWidth !== 0) {
                    downloadCard(img);
                } else {
                    const fallbackImg = new Image();
                    fallbackImg.crossOrigin = 'anonymous';
                    fallbackImg.onload = () => downloadCard(fallbackImg);
                    fallbackImg.onerror = () => {
                        const a = document.createElement('a');
                        a.href = img.src;
                        a.download = 'QRIS-Developer-AkuOnline.svg';
                        a.click();
                    };
                    fallbackImg.src = img.src;
                }
            });
        }
    })();
</script>
