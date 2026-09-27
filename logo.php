<?php
if (!count(debug_backtrace())) {
    header('Content-Type: image/svg+xml');
}
?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="100%" height="100%">
  <defs>
    <!-- Background Gradient -->
    <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#EFF6FF"/>
      <stop offset="50%" stop-color="#DBEAFE"/>
      <stop offset="100%" stop-color="#BFDBFE"/>
    </linearGradient>

    <!-- Robot Body Gloss / Gradient -->
    <linearGradient id="robotWhite" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#FFFFFF"/>
      <stop offset="85%" stop-color="#F1F5F9"/>
      <stop offset="100%" stop-color="#E2E8F0"/>
    </linearGradient>

    <!-- Primary Civic Blue -->
    <linearGradient id="primaryBlue" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#3B82F6"/>
      <stop offset="100%" stop-color="#1D4ED8"/>
    </linearGradient>

    <!-- Visor Face Gradient -->
    <linearGradient id="visorGrad" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#0F172A"/>
      <stop offset="100%" stop-color="#1E293B"/>
    </linearGradient>

    <!-- Cyan Glowing Eyes -->
    <linearGradient id="cyanEye" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#67E8F9"/>
      <stop offset="100%" stop-color="#06B6D4"/>
    </linearGradient>

    <!-- Ear Caps Gradient -->
    <linearGradient id="earBlue" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#60A5FA"/>
      <stop offset="100%" stop-color="#2563EB"/>
    </linearGradient>

    <!-- Soft Drop Shadow Filter -->
    <filter id="softShadow" x="-20%" y="-20%" width="140%" height="140%">
      <feDropShadow dx="0" dy="16" stdDeviation="24" flood-color="#1E3A8A" flood-opacity="0.15"/>
    </filter>

    <!-- Glow Effect for Eyes -->
    <filter id="eyeGlow" x="-30%" y="-30%" width="160%" height="160%">
      <feGaussianBlur stdDeviation="5" result="blur"/>
      <feComposite in="SourceGraphic" in2="blur" operator="over"/>
    </filter>
  </defs>

  <!-- Rounded App Icon Background -->
  <rect width="512" height="512" rx="128" fill="url(#bgGrad)"/>
  
  <!-- Subtle Civic Decorative Concentric Ring -->
  <circle cx="256" cy="256" r="210" fill="none" stroke="#FFFFFF" stroke-width="4" opacity="0.6"/>
  <circle cx="256" cy="256" r="185" fill="none" stroke="#93C5FD" stroke-width="2" stroke-dasharray="8 8" opacity="0.5"/>

  <!-- Robot Mascot Main Group with Shadow -->
  <g filter="url(#softShadow)">
    
    <!-- Little Cute Antenna -->
    <path d="M 256 128 L 256 94" stroke="#94A3B8" stroke-width="8" stroke-linecap="round"/>
    <circle cx="256" cy="84" r="16" fill="url(#primaryBlue)"/>
    <circle cx="256" cy="84" r="7" fill="#60A5FA"/>
    <circle cx="252" cy="80" r="3" fill="#FFFFFF"/>

    <!-- Left & Right Cute Headphone Ears -->
    <!-- Left Ear -->
    <rect x="76" y="210" width="30" height="76" rx="15" fill="url(#earBlue)"/>
    <rect x="88" y="222" width="12" height="52" rx="6" fill="#1D4ED8"/>
    <!-- Right Ear -->
    <rect x="406" y="210" width="30" height="76" rx="15" fill="url(#earBlue)"/>
    <rect x="412" y="222" width="12" height="52" rx="6" fill="#1D4ED8"/>

    <!-- Robot Head Outline / Shell -->
    <rect x="96" y="120" width="320" height="246" rx="90" fill="url(#robotWhite)"/>
    
    <!-- Inner Dark Visor Face Screen -->
    <rect x="126" y="152" width="260" height="182" rx="60" fill="url(#visorGrad)"/>

    <!-- Visor Glass Reflection Highlight (Subtle glossy shine) -->
    <path d="M 148 168 Q 256 182 364 168 A 48 48 0 0 1 376 195 Q 256 205 136 195 A 48 48 0 0 1 148 168 Z" fill="#FFFFFF" opacity="0.08"/>

    <!-- Cute Smiling Glowing Eyes (Happy Curved Arc / Pill Expression) -->
    <!-- Left Eye -->
    <g filter="url(#eyeGlow)">
      <ellipse cx="196" cy="232" rx="26" ry="34" fill="url(#cyanEye)"/>
      <!-- Sparkle in Left Eye -->
      <circle cx="188" cy="222" r="8" fill="#FFFFFF"/>
      <circle cx="206" cy="246" r="4" fill="#FFFFFF" opacity="0.8"/>
    </g>

    <!-- Right Eye -->
    <g filter="url(#eyeGlow)">
      <ellipse cx="316" cy="232" rx="26" ry="34" fill="url(#cyanEye)"/>
      <!-- Sparkle in Right Eye -->
      <circle cx="308" cy="222" r="8" fill="#FFFFFF"/>
      <circle cx="326" cy="246" r="4" fill="#FFFFFF" opacity="0.8"/>
    </g>

    <!-- Cute Glowing Cheeks (Blushing) -->
    <ellipse cx="162" cy="274" rx="16" ry="10" fill="#F43F5E" opacity="0.45"/>
    <ellipse cx="350" cy="274" rx="16" ry="10" fill="#F43F5E" opacity="0.45"/>

    <!-- Friendly Little Smile -->
    <path d="M 234 266 Q 256 284 278 266" fill="none" stroke="#22D3EE" stroke-width="7" stroke-linecap="round"/>

    <!-- Robot Body Preview / Collar underneath -->
    <path d="M 176 364 L 336 364 C 360 364 374 386 366 408 L 358 428 C 352 442 338 452 322 452 L 190 452 C 174 452 160 442 154 428 L 146 408 C 138 386 152 364 176 364 Z" fill="url(#robotWhite)"/>
    
    <!-- Chest Badge / Civic AI Logo Accent (Shield / Heart Civic Emblem) -->
    <rect x="232" y="380" width="48" height="42" rx="14" fill="url(#primaryBlue)"/>
    <path d="M 256 390 L 268 402 L 256 414 L 244 402 Z" fill="#67E8F9"/>
    <!-- Small Upward Chevron (Community Progress) -->
    <path d="M 250 404 L 256 398 L 262 404" fill="none" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>

  </g>
</svg>
