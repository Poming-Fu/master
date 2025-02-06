<?php
header("HTTP/1.1 301 Moved Permanently");
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>頁面已移動</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .countdown {
            font-size: 3rem;
            font-weight: bold;
            color: #dc3545;
        }
        .card {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                        </div>
                        <h2 class="card-title mb-4">網頁已搬遷</h2>
                        
                        <div class="alert alert-info mb-4" role="alert">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            新網址：
                            <a href="/web1/index.php" class="alert-link">/web1/index.php</a>
                        </div>
                        
                        <div class="mb-4">
                            <span class="countdown" id="countdown">5</span>
                            <p class="text-muted">秒後自動轉向</p>
                        </div>
                        
                        <div class="progress mb-4" style="height: 10px;">
                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                        </div>

                        <a href="/web1/index.php" class="btn btn-primary">
                            <i class="bi bi-arrow-right-circle me-2"></i>立即前往
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let seconds = 5;
        const countdownElement = document.getElementById('countdown');
        const progressBar = document.getElementById('progress-bar');
        
        const countdown = setInterval(() => {
            seconds--;
            countdownElement.textContent = seconds;
            
            // 更新進度條
            const percentage = (seconds / 5) * 100;
            progressBar.style.width = `${percentage}%`;
            
            if (seconds <= 0) {
                clearInterval(countdown);
                window.location.href = '/web1/index.php';
            }
        }, 1000);
    </script>
</body>
</html>