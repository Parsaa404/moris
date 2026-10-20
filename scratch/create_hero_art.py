"""
Generate high-fidelity SVG artistic sketch illustrations matching the reference layout.
"""
import os

images_dir = r"c:\Users\vatan\Desktop\Mori\moris-website\assets\images"
os.makedirs(images_dir, exist_ok=True)

# 1. Hero Boxer Sketch (Section B)
# Male boxer with Mori's features (beard, dark hair) throwing a dynamic punch forward
hero_svg = '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 680" width="100%" height="100%" preserveAspectRatio="xMidYMid meet">
  <defs>
    <linearGradient id="heroGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#111317" stop-opacity="0.95"/>
      <stop offset="60%" stop-color="#1e2229" stop-opacity="0.85"/>
      <stop offset="100%" stop-color="#2a303a" stop-opacity="0.7"/>
    </linearGradient>
    <filter id="inkGlow" x="-20%" y="-20%" width="140%" height="140%">
      <feGaussianBlur stdDeviation="3" result="blur"/>
      <feComposite in="SourceGraphic" in2="blur" operator="over"/>
    </filter>
  </defs>

  <!-- Background subtle texture and motion arcs -->
  <g stroke="#3a404d" stroke-width="1.5" fill="none" opacity="0.35">
    <ellipse cx="680" cy="380" rx="420" ry="260" stroke-dasharray="14 8"/>
    <ellipse cx="720" cy="340" rx="360" ry="220" stroke-dasharray="6 6"/>
    <path d="M 450,120 Q 750,220 920,440" stroke-width="2"/>
    <path d="M 400,200 Q 700,320 880,560" stroke-width="2.5"/>
  </g>

  <!-- Dynamic impact and action lines behind punch -->
  <g stroke="#0abab5" stroke-width="2" opacity="0.45" stroke-linecap="round">
    <line x1="560" y1="360" x2="880" y2="400" stroke-width="3"/>
    <line x1="580" y1="320" x2="940" y2="340" stroke-width="2"/>
    <line x1="570" y1="410" x2="900" y2="460" stroke-width="2.5"/>
    <line x1="530" y1="280" x2="820" y2="280" stroke-width="1.5"/>
    <line x1="600" y1="440" x2="860" y2="520" stroke-width="1.5"/>
  </g>

  <!-- Massive Foreground Punch / Boxing Glove (Extreme Perspective) -->
  <!-- Forearm and Wrist with Hand Wraps -->
  <path d="M 460,340 C 490,320 540,310 610,320 C 670,330 730,350 820,380 C 850,390 870,430 840,470 C 800,520 740,540 660,520 C 580,500 520,450 470,410 Z" 
        fill="#1a1c22" stroke="#2c323c" stroke-width="4"/>

  <!-- Glove Padding & Main Shape -->
  <path d="M 620,300 C 680,260 780,270 850,310 C 910,345 940,400 920,460 C 900,520 830,560 750,550 C 680,540 620,490 600,430 C 590,390 600,340 620,300 Z" 
        fill="#15171c" stroke="#d5dbe0" stroke-width="3.5" filter="url(#inkGlow)"/>

  <!-- Glove highlights and contour creases -->
  <path d="M 660,330 C 720,300 790,310 840,340 C 880,365 890,410 880,450 C 865,490 810,515 760,510" 
        fill="none" stroke="#e8ecef" stroke-width="2.5" stroke-linecap="round"/>
  <path d="M 700,350 C 750,330 810,340 845,370" fill="none" stroke="#0abab5" stroke-width="3" stroke-linecap="round"/>
  
  <!-- Glove Thumb and Seams -->
  <path d="M 640,400 C 660,440 700,480 750,485 C 780,488 790,460 765,435 C 740,410 690,390 640,400 Z" 
        fill="#22262e" stroke="#c0c7cf" stroke-width="2"/>
  <line x1="720" y1="360" x2="840" y2="470" stroke="#0abab5" stroke-width="1.8" stroke-dasharray="6 4"/>
  <line x1="680" y1="410" x2="790" y2="500" stroke="#9ba3af" stroke-width="1.5"/>

  <!-- Hand wraps at the wrist -->
  <g stroke="#e2e8f0" stroke-width="2.5" fill="none">
    <path d="M 540,345 C 570,335 600,340 620,360"/>
    <path d="M 525,370 C 555,360 590,365 615,385"/>
    <path d="M 510,395 C 540,385 575,395 600,415"/>
    <path d="M 495,420 C 525,410 560,420 585,440"/>
  </g>

  <!-- Boxer Body / Torso / Arms in Guard -->
  <!-- Left Shoulder & Upper Arm (Punches from boxer's right / our left) -->
  <path d="M 360,260 C 400,280 440,300 480,340 C 460,370 430,390 390,380 C 350,370 330,330 330,300 C 330,275 345,265 360,260 Z" 
        fill="#232730" stroke="#3b4250" stroke-width="2.5"/>

  <!-- Boxer's Left Arm in High Defensive Guard (Close to jaw) -->
  <path d="M 280,240 C 300,210 330,200 350,220 C 370,240 375,280 360,320 C 340,360 300,380 270,350 C 250,330 250,280 280,240 Z" 
        fill="#1e2229" stroke="#4a5263" stroke-width="2"/>
  <!-- Left Glove guarding face -->
  <path d="M 310,190 C 340,180 370,195 380,225 C 385,250 370,270 345,275 C 320,280 295,265 295,235 C 295,210 300,195 310,190 Z" 
        fill="#14161b" stroke="#9ba3af" stroke-width="2"/>

  <!-- Chest & Athletic Torso -->
  <path d="M 260,340 C 300,360 350,370 420,380 C 440,430 450,490 440,560 C 380,580 300,580 240,540 C 230,480 240,400 260,340 Z" 
        fill="#181a20" stroke="#333a47" stroke-width="2"/>
  <!-- Muscular definition / crosshatching -->
  <g stroke="#4f596b" stroke-width="1.5" fill="none" opacity="0.6">
    <path d="M 320,380 C 345,410 350,450 340,480"/>
    <path d="M 370,390 C 390,420 395,460 385,490"/>
    <line x1="310" y1="420" x2="335" y2="435"/>
    <line x1="305" y1="440" x2="330" y2="455"/>
    <line x1="360" y1="425" x2="385" y2="440"/>
    <line x1="355" y1="445" x2="380" y2="460"/>
  </g>

  <!-- Boxer's Head & Face (Mori: sharp jaw, neat beard, dark slick athletic hair, intense eyes) -->
  <!-- Neck -->
  <path d="M 310,210 C 325,235 330,265 330,290 C 295,285 275,270 265,245 Z" fill="#20242c" stroke="#373e4b" stroke-width="1.8"/>

  <!-- Head Base & Jaw -->
  <path d="M 270,110 C 310,105 345,120 360,150 C 370,175 365,210 340,235 C 315,255 285,250 265,230 C 245,210 240,170 250,140 C 255,120 265,112 270,110 Z" 
        fill="#1e2229" stroke="#3e4654" stroke-width="2"/>

  <!-- Athletic Slick Hair with Texture Strokes -->
  <path d="M 250,135 C 245,100 265,70 300,65 C 335,60 365,75 375,105 C 380,125 375,145 360,155 C 350,130 330,115 300,115 C 275,115 260,125 250,135 Z" 
        fill="#0f1115" stroke="#474f5e" stroke-width="2"/>
  <g stroke="#9ba3af" stroke-width="1.2" fill="none" opacity="0.75">
    <path d="M 270,85 C 295,80 325,85 345,100"/>
    <path d="M 285,75 C 310,72 340,80 360,95"/>
    <path d="M 265,100 C 285,95 315,100 335,112"/>
    <path d="M 305,68 C 325,72 350,85 365,110"/>
  </g>

  <!-- Facial Features: Brows, Eyes, Nose, Beard -->
  <!-- Brow line (focused, aggressive boxing focus) -->
  <path d="M 290,148 Q 308,144 322,148" stroke="#f1f5f9" stroke-width="2.5" stroke-linecap="round" fill="none"/>
  <path d="M 332,150 Q 346,148 356,155" stroke="#f1f5f9" stroke-width="2.2" stroke-linecap="round" fill="none"/>
  <!-- Eyes -->
  <ellipse cx="308" cy="155" rx="5" ry="3.2" fill="#f8fafc"/>
  <circle cx="310" cy="155" r="2.2" fill="#0c0d10"/>
  <ellipse cx="342" cy="157" rx="4.5" ry="3" fill="#f8fafc"/>
  <circle cx="343" cy="157" r="2" fill="#0c0d10"/>

  <!-- Nose bridge & tip -->
  <path d="M 322,148 L 320,175 L 328,178" fill="none" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round"/>

  <!-- Sharp Trimmed Beard & Mustache -->
  <path d="M 285,188 C 295,185 315,185 328,188 C 330,195 325,202 312,202 C 298,202 288,195 285,188 Z" fill="#0f1115"/>
  <path d="M 265,170 C 265,205 280,240 310,245 C 330,245 348,225 355,195 C 350,210 335,232 312,232 C 288,232 272,210 270,185 Z" 
        fill="#0f1115" stroke="#333a45" stroke-width="1.5"/>

  <!-- High-impact speed / kinetic lines pointing toward viewer -->
  <g stroke="#ffffff" stroke-width="1.5" opacity="0.4" stroke-linecap="round">
    <line x1="880" y1="330" x2="980" y2="310"/>
    <line x1="910" y1="390" x2="1000" y2="390"/>
    <line x1="890" y1="460" x2="990" y2="480"/>
    <line x1="840" y1="530" x2="940" y2="570"/>
  </g>
</svg>'''

with open(os.path.join(images_dir, "hero-boxer-sketch.svg"), "w", encoding="utf-8") as f:
    f.write(hero_svg)

print("Created hero-boxer-sketch.svg")
