<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Access Denied | Engage Reward</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
:root {
    --primary: #2F54EB;
    --primary-dark: #2B50D9;
    --accent: #3B6CFF;
    --bg: #F4F7FB;
    --white: #FFFFFF;
    --text-dark: #1F2937;
    --text-muted: #6B7280;
}

/* Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Inter", system-ui, sans-serif;
}

body {
    height: 100vh;
    background: linear-gradient(120deg, #eef2ff, #f4f7fb);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

/* Soft animated background glow */
.glow {
    position: absolute;
    width: 380px;
    height: 380px;
    background: var(--accent);
    opacity: 0.12;
    filter: blur(140px);
    animation: float 8s ease-in-out infinite alternate;
}

@keyframes float {
    from { transform: translateY(0); }
    to { transform: translateY(50px); }
}

/* Card */
.card {
    position: relative;
    background: var(--white);
    width: 420px;
    padding: 45px 35px;
    border-radius: 18px;
    box-shadow: 0 25px 50px rgba(0,0,0,0.08);
    text-align: center;
    animation: fadeIn 0.8s ease forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Lock animation */
.lock-wrapper {
    width: 90px;
    height: 90px;
    margin: 0 auto 25px;
    animation: hop 2s ease-in-out infinite;
}

@keyframes hop {
    0% { transform: translateY(0); }
    20% { transform: translateY(-14px); }
    40% { transform: translateY(0); }
    50% { transform: translateX(-3px); }
    60% { transform: translateX(3px); }
    70% { transform: translateX(-3px); }
    80% { transform: translateX(3px); }
    100% { transform: translateX(0); }
}

.lock {
    width: 90px;
    height: 65px;
    background: var(--primary);
    border-radius: 12px;
    position: relative;
}

.lock::before {
    content: "";
    position: absolute;
    width: 45px;
    height: 35px;
    border: 6px solid var(--primary);
    border-bottom: none;
    border-radius: 22px 22px 0 0;
    top: -35px;
    left: 50%;
    transform: translateX(-50%);
    background: transparent;
}

.keyhole {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 12px;
    height: 12px;
    background: #fff;
    border-radius: 50%;
}

.keyhole::after {
    content: "";
    position: absolute;
    width: 4px;
    height: 10px;
    background: #fff;
    top: 12px;
    left: 50%;
    transform: translateX(-50%);
}

/* Text */
h1 {
    font-size: 22px;
    color: var(--text-dark);
    margin-bottom: 10px;
}

p {
    font-size: 14px;
    color: var(--text-muted);
    line-height: 1.6;
}

/* Footer */
.footer {
    margin-top: 28px;
    font-size: 12px;
    color: var(--text-muted);
}

.login-btn {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 30px;
    background: var(--primary);
    color: var(--white);
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

/* Responsive */
@media (max-width: 480px) {
    .card {
        width: 90%;
        padding: 35px 25px;
    }
}

</style>
</head>

<body>

<div class="glow"></div>

<div class="card">
    <div class="lock-wrapper">
        <div class="lock">
            <div class="keyhole"></div>
        </div>
    </div>

    <h1>Access Restricted</h1>
    <p>
        You’re not authorized to view this page.<br>
        Please login to continue using <strong>Engage Reward</strong>.
    </p>

     <button class="login-btn" onclick="window.location.href='/login'">Go to Login</button>

    <div class="footer">
        © 2025 Engage Reward
    </div>
</div>

</body>
</html>
