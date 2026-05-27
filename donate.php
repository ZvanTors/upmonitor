<?php
// donate.php
define('PAGE_TITLE', 'Donate');
require_once 'includes/config.php';
require_once 'includes/auth.php';
// (اختیاری) می‌توانی لاگین اجباری کنی
// if (!isLoggedIn()) { header('Location: login.php'); exit; }
?>
<?php include 'templates/header.php'; ?>

<div class="row justify-content-center text-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body py-5">
                <i class="fas fa-heart text-danger display-1 mb-3"></i>
                <h1 class="display-5">Support UpMonitor</h1>
                <p class="lead text-muted">Your crypto donations keep this project alive and ad-free.</p>

                <!-- TRX (Tron) -->
                <div class="card bg-body-tertiary mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fab fa-tron"></i> TRX (Tron)</h5>
                        <div class="input-group mb-2">
                            <span class="input-group-text">Address</span>
                            <input type="text" class="form-control font-monospace" id="trxAddress" 
                                   value="TWukNBmxLUbPVgayRsZ72u84K8yW7K9cQw" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyAddress('trxAddress')">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                        <small class="text-muted">Network: Tron (TRC20) – Low fees</small>
                    </div>
                </div>

                <!-- USDT (TRC20) - Tron network -->
                <div class="card bg-body-tertiary mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-dollar-sign"></i> USDT (TRC20)</h5>
                        <div class="input-group mb-2">
                            <span class="input-group-text">Address</span>
                            <input type="text" class="form-control font-monospace" id="usdtTrcAddress" 
                                   value="TWukNBmxLUbPVgayRsZ72u84K8yW7K9cQw" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyAddress('usdtTrcAddress')">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                        <small class="text-muted">Network: Tron (TRC20)</small>
                    </div>
                </div>

                <!-- USDT (BEP20) - BSC network -->
                <div class="card bg-body-tertiary mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-dollar-sign"></i> USDT (BEP20)</h5>
                        <div class="input-group mb-2">
                            <span class="input-group-text">Address</span>
                            <input type="text" class="form-control font-monospace" id="usdtBepAddress" 
                                   value="0x53f03070E2b6157fCaF48688b3426fA131c8175B" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyAddress('usdtBepAddress')">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                        <small class="text-muted">Network: BNB Smart Chain (BEP20) – Lowest fees ✅</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- اسکریپت کپی -->
<script>
function copyAddress(elementId) {
    var copyText = document.getElementById(elementId);
    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices
    navigator.clipboard.writeText(copyText.value).then(function() {
        // می‌توانی یک tooltip نشان دهی
        alert('Address copied to clipboard!');
    }).catch(function(err) {
        // اگر clipboard API کار نکرد، از روش قدیمی استفاده کن
        document.execCommand('copy');
        alert('Address copied (fallback).');
    });
}
</script>

<?php include 'templates/footer.php'; ?>