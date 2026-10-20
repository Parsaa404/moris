"""
Generate all illustrations for Mori website layout redesign.
"""
import os

images_dir = r"c:\Users\vatan\Desktop\Mori\moris-website\assets\images"
os.makedirs(images_dir, exist_ok=True)

# 1. Hero SVG
with open(os.path.join(images_dir, "hero-boxer-sketch.svg"), "w", encoding="utf-8") as f:
    f.write('''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 640" width="100%" height="100%" preserveAspectRatio="xMidYMid meet">
  <defs>
    <radialGradient id="punchGlow" cx="72%" cy="58%" r="45%">
      <stop offset="0%" stop-color="#0abab5" stop-opacity="0.25"/>
      <stop offset="60%" stop-color="#14181f" stop-opacity="0.1"/>
      <stop offset="100%" stop-color="#0c0d10" stop-opacity="0"/>
    </radialGradient>
  </defs>

  <rect width="1000" height="640" fill="transparent"/>
  <circle cx="720" cy="380" r="320" fill="url(#punchGlow)"/>

  <!-- Dramatic crosshatch arcs & shadow plane -->
  <g stroke="#7a8494" stroke-width="1.2" opacity="0.3" fill="none">
    <ellipse cx="620" cy="520" rx="360" ry="80"/>
    <ellipse cx="620" cy="540" rx="280" ry="60" stroke-dasharray="8 6"/>
    <path d="M 120,480 Q 400,560 850,530"/>
  </g>

  <!-- Kinetic speed arcs -->
  <g stroke="#cbd5e1" stroke-width="1.5" opacity="0.45" fill="none" stroke-linecap="round">
    <path d="M 440,240 C 560,260 740,310 880,360" stroke-width="2.2"/>
    <path d="M 400,290 C 540,320 720,380 910,430" stroke-width="3"/>
    <path d="M 430,340 C 560,370 700,430 860,500" stroke-width="2"/>
    <path d="M 520,200 C 640,230 780,270 890,320" stroke-dasharray="10 6"/>
    <path d="M 480,420 C 600,460 740,510 870,550" stroke-dasharray="12 6"/>
  </g>

  <!-- Dramatic Boxer Body & Guard (Dark athletic charcoal silhouette with highlights) -->
  <!-- Left shoulder, back muscular plane -->
  <path d="M 220,360 C 260,340 310,345 340,370 C 370,395 380,440 360,520 C 320,550 250,560 210,510 C 190,470 190,400 220,360 Z" 
        fill="#181b22" stroke="#333a46" stroke-width="2"/>
  
  <!-- Left Arm (Rear guard tight against chin) -->
  <path d="M 240,240 C 260,210 290,200 315,215 C 335,235 340,270 330,310 C 315,350 280,370 250,340 C 230,315 225,270 240,240 Z" 
        fill="#15171d" stroke="#3e4654" stroke-width="2"/>
  <!-- Guarding Glove (Left hand protecting jaw) -->
  <path d="M 285,185 C 315,175 345,190 355,220 C 360,245 345,268 320,272 C 295,275 275,255 275,230 C 275,205 280,190 285,185 Z" 
        fill="#111317" stroke="#94a3b8" stroke-width="2"/>
  <path d="M 295,195 C 320,188 340,200 348,222" fill="none" stroke="#0abab5" stroke-width="2"/>

  <!-- Muscular Neck & Chin -->
  <path d="M 275,220 C 290,240 298,270 295,295 C 270,290 250,275 240,250 Z" fill="#1c1f26" stroke="#2e3541" stroke-width="1.5"/>

  <!-- Head Base, Jawline, Cheekbone -->
  <path d="M 240,115 C 275,108 315,120 330,150 C 342,175 338,212 315,236 C 290,255 260,250 242,230 C 225,210 220,170 228,140 C 232,122 238,116 240,115 Z" 
        fill="#181b22" stroke="#475569" stroke-width="2"/>

  <!-- Athletic Slicked Back Hair -->
  <path d="M 225,135 C 218,100 240,68 275,62 C 310,58 340,72 350,102 C 355,122 350,142 335,152 C 325,128 305,112 275,112 C 250,112 235,122 225,135 Z" 
        fill="#0c0d10" stroke="#334155" stroke-width="2"/>
  <g stroke="#94a3b8" stroke-width="1.2" fill="none" opacity="0.8">
    <path d="M 245,85 C 270,80 300,85 320,100"/>
    <path d="M 260,75 C 285,72 315,80 335,95"/>
    <path d="M 240,100 C 260,95 290,100 310,112"/>
    <path d="M 280,68 C 300,72 325,85 340,110"/>
  </g>

  <!-- Focused Boxing Eyes & Intense Brow -->
  <path d="M 265,148 Q 282,143 296,148" stroke="#f8fafc" stroke-width="2.5" stroke-linecap="round" fill="none"/>
  <path d="M 306,150 Q 320,147 330,154" stroke="#f8fafc" stroke-width="2.2" stroke-linecap="round" fill="none"/>
  <ellipse cx="282" cy="155" rx="5" ry="3.2" fill="#f8fafc"/>
  <circle cx="284" cy="155" r="2.2" fill="#0c0d10"/>
  <ellipse cx="316" cy="157" rx="4.5" ry="3" fill="#f8fafc"/>
  <circle cx="317" cy="157" r="2" fill="#0c0d10"/>
  <path d="M 296,148 L 294,175 L 302,178" fill="none" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round"/>

  <!-- Trimmed Groomed Beard & Mustache (Mori's signature look) -->
  <path d="M 260,188 C 270,185 290,185 302,188 C 304,195 298,202 286,202 C 274,202 264,195 260,188 Z" fill="#0c0d10"/>
  <path d="M 240,170 C 240,205 255,240 284,245 C 304,245 322,225 328,195 C 324,210 310,232 286,232 C 262,232 248,210 245,185 Z" 
        fill="#0c0d10" stroke="#334155" stroke-width="1.5"/>

  <!-- Right Arm Delivering the Colossal Lead Cross (Perspective Foreshortening) -->
  <!-- Right Shoulder / Deltoid -->
  <path d="M 315,310 C 350,290 400,280 460,290 C 510,300 550,330 550,370 C 520,410 450,420 390,400 C 340,380 320,340 315,310 Z" 
        fill="#1e222a" stroke="#3b4352" stroke-width="2.5"/>
  <path d="M 380,315 C 430,305 480,315 510,345" fill="none" stroke="#0abab5" stroke-width="1.8" opacity="0.6"/>

  <!-- Forearm Extending Directly Outward with Wraps -->
  <path d="M 480,310 C 540,300 620,310 700,335 C 730,345 745,395 710,435 C 650,470 570,475 500,430 C 470,400 460,350 480,310 Z" 
        fill="#16181f" stroke="#2d3340" stroke-width="3"/>

  <!-- Athletic Wraps at Wrist -->
  <g stroke="#f1f5f9" stroke-width="2.5" fill="none">
    <path d="M 600,320 C 625,315 655,325 675,348"/>
    <path d="M 585,342 C 610,335 640,348 662,370"/>
    <path d="M 570,365 C 595,358 628,370 650,392"/>
    <path d="M 555,390 C 580,382 612,395 635,418"/>
  </g>

  <!-- Massive Boxing Glove in Foreground (Dominant Focal Point) -->
  <!-- Glove Main Shape -->
  <path d="M 670,280 C 750,230 870,240 940,290 C 990,335 1010,410 970,480 C 930,550 830,580 750,560 C 680,540 640,470 650,400 C 655,350 660,305 670,280 Z" 
        fill="#0f1115" stroke="#f8fafc" stroke-width="4"/>

  <!-- Glove Padding Form Highlights -->
  <path d="M 720,310 C 790,265 880,275 930,315 C 970,345 985,405 960,455 C 935,505 860,535 790,525" 
        fill="none" stroke="#e2e8f0" stroke-width="3" stroke-linecap="round"/>
  <!-- Vibrant Accent Stroke on Glove Apex -->
  <path d="M 760,335 C 830,295 910,310 945,345" fill="none" stroke="#0abab5" stroke-width="4.5" stroke-linecap="round"/>
  <path d="M 780,360 C 850,325 920,340 940,375" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" opacity="0.8"/>

  <!-- Thumb Contour & Seams -->
  <path d="M 690,400 C 720,445 770,490 825,490 C 860,490 870,460 840,430 C 805,395 750,380 690,400 Z" 
        fill="#1c2027" stroke="#cbd5e1" stroke-width="2.5"/>
  <line x1="780" y1="340" x2="900" y2="460" stroke="#0abab5" stroke-width="2" stroke-dasharray="8 6"/>
  <line x1="730" y1="390" x2="850" y2="500" stroke="#64748b" stroke-width="2"/>

  <!-- Speed bursts and kinetic impact sparks -->
  <g stroke="#ffffff" stroke-width="2.5" stroke-linecap="round">
    <line x1="940" y1="280" x2="995" y2="250"/>
    <line x1="975" y1="340" x2="1030" y2="330"/>
    <line x1="985" y1="410" x2="1040" y2="420"/>
    <line x1="960" y1="480" x2="1015" y2="510"/>
    <line x1="900" y1="550" x2="955" y2="595"/>
  </g>
</svg>''')

# 2. Calligraphy Artwork (Section C - Left side)
with open(os.path.join(images_dir, "calligraphy-art.svg"), "w", encoding="utf-8") as f:
    f.write('''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 700" width="100%" height="100%" preserveAspectRatio="xMidYMid meet">
  <defs>
    <linearGradient id="calGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#0c0d10" stop-opacity="0.85"/>
      <stop offset="70%" stop-color="#1e293b" stop-opacity="0.75"/>
      <stop offset="100%" stop-color="#0abab5" stop-opacity="0.9"/>
    </linearGradient>
  </defs>

  <!-- Large flourishing Persian / Arabic calligraphic art composition -->
  <!-- Backing subtle ornamental arcs -->
  <g stroke="#e2e8f0" stroke-width="1.5" fill="none" opacity="0.6">
    <circle cx="280" cy="350" r="220" stroke-dasharray="16 10"/>
    <circle cx="280" cy="350" r="160" stroke-dasharray="8 8"/>
    <path d="M 80,180 Q 280,80 480,180" stroke-width="2"/>
    <path d="M 80,520 Q 280,620 480,520" stroke-width="2"/>
  </g>

  <!-- Major Calligraphic Strokes (Siyah Mashq / Nastaliq Flowing Letterforms) -->
  <g fill="url(#calGrad)" stroke="#0c0d10" stroke-width="1.5">
    <!-- Sweeping Alef & Lam vertical strokes -->
    <path d="M 220,90 C 235,90 245,150 240,280 C 235,410 215,530 140,580 C 120,595 105,580 120,560 C 160,510 185,410 190,280 C 195,150 205,90 220,90 Z"/>
    <path d="M 280,70 C 295,70 305,130 300,260 C 295,390 280,510 210,590 C 190,610 175,600 190,575 C 230,510 250,390 255,260 C 260,130 265,70 280,70 Z"/>

    <!-- Grand horizontal sweeping strokes (Kashida) -->
    <path d="M 80,310 C 160,290 280,270 420,295 C 490,310 540,345 560,380 C 565,395 550,405 530,395 C 490,370 440,340 370,330 C 260,315 150,340 80,370 C 65,375 60,360 68,345 C 72,330 75,315 80,310 Z"/>

    <!-- Persian curved 'Raa' & 'Vav' flourishes -->
    <path d="M 330,220 C 370,210 420,230 450,270 C 475,310 460,370 410,420 C 350,480 260,520 160,540 C 130,545 125,530 145,520 C 220,495 300,455 350,405 C 390,365 400,325 385,295 C 370,270 340,255 315,260 Z"/>

    <!-- Dynamic loop strokes ('Ain / Ghayn / Haa') -->
    <path d="M 260,380 C 290,350 340,350 370,385 C 395,415 390,460 350,490 C 310,520 240,530 180,520 C 150,515 160,495 185,498 C 235,502 290,495 320,470 C 345,450 350,420 330,400 C 310,380 280,380 255,400 Z"/>

    <!-- Secondary flourish layers overlapping with cyan accent hints -->
    <path d="M 360,160 C 410,150 460,170 490,210 C 510,240 500,280 460,310 C 420,335 370,330 330,300 C 300,275 310,235 335,200 C 345,185 352,170 360,160 Z" opacity="0.85"/>
    <path d="M 170,240 C 210,225 250,230 270,260 C 285,285 275,315 245,330 C 215,345 175,340 150,315 C 130,295 135,265 155,248 Z" opacity="0.75"/>
  </g>

  <!-- Accent calligraphic dots (Nuqta) - diamond shaped -->
  <g fill="#0abab5">
    <polygon points="340,110 360,130 340,150 320,130"/>
    <polygon points="375,125 395,145 375,165 355,145"/>
    <polygon points="260,490 280,510 260,530 240,510"/>
    <polygon points="420,440 440,460 420,480 400,460"/>
  </g>

  <!-- Delicate ink splatters & calligraphic texture -->
  <g fill="#0c0d10" opacity="0.6">
    <circle cx="130" cy="270" r="4"/>
    <circle cx="115" cy="290" r="2.5"/>
    <circle cx="480" cy="260" r="3.5"/>
    <circle cx="515" cy="285" r="2"/>
    <circle cx="310" cy="570" r="4"/>
    <circle cx="335" cy="585" r="2.5"/>
    <circle cx="420" cy="180" r="3"/>
  </g>
</svg>''')

# 3. Sparring Boxers Sketch (Section D)
with open(os.path.join(images_dir, "sparring-boxers-sketch.svg"), "w", encoding="utf-8") as f:
    f.write('''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 560" width="100%" height="100%" preserveAspectRatio="xMidYMid meet">
  <defs>
    <radialGradient id="ringGlow" cx="50%" cy="50%" r="50%">
      <stop offset="0%" stop-color="#0abab5" stop-opacity="0.12"/>
      <stop offset="80%" stop-color="#ffffff" stop-opacity="0"/>
    </radialGradient>
  </defs>

  <rect width="900" height="560" fill="transparent"/>
  <circle cx="450" cy="280" r="280" fill="url(#ringGlow)"/>

  <!-- Ring ropes / background lines -->
  <g stroke="#cbd5e1" stroke-width="1.5" opacity="0.6">
    <line x1="80" y1="200" x2="820" y2="180"/>
    <line x1="80" y1="280" x2="820" y2="260"/>
    <line x1="80" y1="360" x2="820" y2="340"/>
  </g>

  <!-- Canvas Floor Perspective -->
  <g stroke="#e2e8f0" stroke-width="1.2" opacity="0.7">
    <line x1="200" y1="500" x2="350" y2="380"/>
    <line x1="450" y1="520" x2="520" y2="380"/>
    <line x1="700" y1="500" x2="650" y2="380"/>
  </g>

  <!-- BOXER 1 (Left: Attacking with Lead Hook / Jab, Mori coaching or sparring) -->
  <g stroke="#1e293b" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round">
    <!-- Head & Hair -->
    <path d="M 280,160 C 290,140 310,135 330,145 C 345,155 350,175 340,195 C 330,215 310,225 290,220 C 275,215 270,195 275,175 Z" fill="#181b22"/>
    <path d="M 290,140 C 310,130 335,138 345,155" stroke="#475569" stroke-width="2"/>
    <!-- Intense gaze -->
    <circle cx="320" cy="175" r="2" fill="#0abab5"/>

    <!-- Torso in Rotation -->
    <path d="M 285,220 C 270,250 260,290 270,340 C 295,350 330,345 355,325 C 365,285 360,245 340,220 Z" fill="#1e222a"/>

    <!-- Left Arm Throwing Cross Toward Partner -->
    <path d="M 335,240 C 375,235 425,240 475,250" stroke-width="4.5" stroke="#0f172a"/>
    <!-- Left Boxing Glove impacting focus mitt -->
    <path d="M 470,230 C 495,220 525,230 535,250 C 540,268 525,282 505,285 C 485,285 470,270 470,245 Z" fill="#0f1115" stroke="#0abab5" stroke-width="2.5"/>

    <!-- Right Guard Arm tucked at chin -->
    <path d="M 295,235 C 290,265 295,290 320,300 C 335,295 340,275 335,250" stroke-width="3"/>
    <circle cx="330" cy="245" r="14" fill="#181b22" stroke="#94a3b8" stroke-width="1.8"/>

    <!-- Athletic Legs in Boxing Stance -->
    <!-- Front Leg -->
    <path d="M 315,340 C 335,385 350,430 365,475 C 370,490 355,505 340,505" stroke-width="3"/>
    <!-- Boxing Shoe -->
    <path d="M 340,505 L 380,505 C 390,505 390,495 375,485 Z" fill="#0f172a"/>

    <!-- Rear Leg Loaded -->
    <path d="M 280,340 C 265,380 250,425 240,470 C 235,485 245,495 260,495" stroke-width="2.8"/>
    <path d="M 240,495 L 275,495 C 285,495 285,485 270,478 Z" fill="#0f172a"/>
  </g>

  <!-- BOXER 2 / TRAINER (Right: Holding Focus Mitts, slipping & timing) -->
  <g stroke="#1e293b" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round">
    <!-- Head & Cap / Headguard -->
    <path d="M 620,150 C 605,135 585,135 570,145 C 555,155 550,175 560,195 C 570,215 590,225 610,220 C 625,215 630,195 625,175 Z" fill="#1e222a"/>
    <path d="M 625,150 L 645,155" stroke-width="2"/> <!-- Cap visor -->

    <!-- Broad Trainer Torso / Sweatshirt -->
    <path d="M 610,220 C 630,250 640,290 630,340 C 605,350 570,345 545,325 C 535,285 540,245 560,220 Z" fill="#181b22"/>

    <!-- Left Arm Holding Focus Pad / Catching Punch -->
    <path d="M 565,240 C 540,235 515,240 495,250" stroke-width="4" stroke="#1e293b"/>
    <!-- Focus Mitt / Pad (Catching lead glove) -->
    <ellipse cx="505" cy="255" rx="18" ry="24" transform="rotate(-15 505 255)" fill="#0abab5" stroke="#0f172a" stroke-width="2.5"/>
    <circle cx="505" cy="255" r="5" fill="#ffffff"/> <!-- Target dot -->

    <!-- Right Focus Mitt in Secondary Position -->
    <path d="M 605,245 C 585,250 565,270 545,285" stroke-width="3.5"/>
    <ellipse cx="540" cy="290" rx="16" ry="22" transform="rotate(20 540 290)" fill="#181b22" stroke="#0abab5" stroke-width="2"/>

    <!-- Stable Coach Footwork -->
    <path d="M 585,340 C 570,385 555,430 540,475 C 535,490 548,505 565,505" stroke-width="3"/>
    <path d="M 540,505 L 580,505 C 590,505 590,495 575,485 Z" fill="#0f172a"/>

    <path d="M 620,340 C 635,380 650,425 660,470 C 665,485 655,495 640,495" stroke-width="2.8"/>
    <path d="M 640,495 L 675,495 C 685,495 685,485 670,478 Z" fill="#0f172a"/>
  </g>

  <!-- Impact Sparks & Motion Wave at Contact Point -->
  <g stroke="#0abab5" stroke-width="2.2" stroke-linecap="round">
    <line x1="495" y1="230" x2="480" y2="215"/>
    <line x1="515" y1="230" x2="530" y2="215"/>
    <line x1="480" y1="260" x2="460" y2="265"/>
    <line x1="520" y1="275" x2="540" y2="285"/>
    <circle cx="500" cy="250" r="14" fill="none" stroke="#0abab5" stroke-width="1.5" stroke-dasharray="4 4"/>
  </g>
</svg>''')

# 4. Coach Mori Sketch (Section F - About / Bio)
with open(os.path.join(images_dir, "coach-mori-sketch.svg"), "w", encoding="utf-8") as f:
    f.write('''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 550 650" width="100%" height="100%" preserveAspectRatio="xMidYMid meet">
  <defs>
    <radialGradient id="coachGlow" cx="50%" cy="35%" r="55%">
      <stop offset="0%" stop-color="#0abab5" stop-opacity="0.18"/>
      <stop offset="70%" stop-color="#14181f" stop-opacity="0.05"/>
      <stop offset="100%" stop-color="#0c0d10" stop-opacity="0"/>
    </radialGradient>
  </defs>

  <rect width="550" height="650" fill="transparent"/>
  <circle cx="280" cy="280" r="260" fill="url(#coachGlow)"/>

  <!-- Athletic Coach Mori leaning forward with warm, confident engaging expression -->
  <!-- Shoulders & Upper Body Silhouette -->
  <path d="M 120,440 C 160,370 210,340 280,340 C 350,340 400,370 440,440 C 470,490 480,560 470,640 L 90,640 C 80,560 90,490 120,440 Z" 
        fill="#111317" stroke="#252b36" stroke-width="2.5"/>

  <!-- T-shirt collar & neck definition -->
  <path d="M 220,350 C 250,375 310,375 340,350" fill="none" stroke="#0abab5" stroke-width="2.5"/>
  <path d="M 235,320 C 250,335 310,335 325,320" fill="none" stroke="#334155" stroke-width="1.8"/>

  <!-- Neck -->
  <path d="M 240,260 C 240,295 250,330 280,335 C 310,330 320,295 320,260 Z" fill="#181c24" stroke="#2d3748" stroke-width="1.8"/>

  <!-- Head & Facial Structure (Mori: friendly smile, neat beard, athletic cheekbones) -->
  <path d="M 205,150 C 205,95 240,65 280,65 C 320,65 355,95 355,150 C 355,210 330,265 280,270 C 230,265 205,210 205,150 Z" 
        fill="#161920" stroke="#3b4454" stroke-width="2"/>

  <!-- Dark styled hair -->
  <path d="M 195,130 C 190,80 230,45 280,45 C 330,45 370,80 365,130 C 350,105 320,90 280,90 C 240,90 210,105 195,130 Z" 
        fill="#0a0c0f" stroke="#475569" stroke-width="2"/>
  <path d="M 220,70 Q 280,55 335,75" fill="none" stroke="#64748b" stroke-width="2"/>
  <path d="M 240,60 Q 280,50 320,65" fill="none" stroke="#94a3b8" stroke-width="1.5"/>

  <!-- Ears -->
  <ellipse cx="205" cy="165" rx="8" ry="16" fill="#181c24" stroke="#2d3748" stroke-width="1.5"/>
  <ellipse cx="355" cy="165" rx="8" ry="16" fill="#181c24" stroke="#2d3748" stroke-width="1.5"/>

  <!-- Eyebrows (engaging, friendly arch) -->
  <path d="M 230,135 Q 250,130 265,135" stroke="#f1f5f9" stroke-width="2.5" stroke-linecap="round" fill="none"/>
  <path d="M 295,135 Q 310,130 330,135" stroke="#f1f5f9" stroke-width="2.5" stroke-linecap="round" fill="none"/>

  <!-- Warm, intelligent eyes with crow's feet smile lines -->
  <ellipse cx="250" cy="148" rx="6" ry="4" fill="#f8fafc"/>
  <circle cx="250" cy="148" r="2.8" fill="#0c0d10"/>
  <ellipse cx="310" cy="148" rx="6" ry="4" fill="#f8fafc"/>
  <circle cx="310" cy="148" r="2.8" fill="#0c0d10"/>
  <!-- Smile lines at corner of eyes -->
  <path d="M 238,148 Q 232,145 230,140" stroke="#64748b" stroke-width="1.2" fill="none"/>
  <path d="M 322,148 Q 328,145 330,140" stroke="#64748b" stroke-width="1.2" fill="none"/>

  <!-- Nose -->
  <path d="M 280,135 L 276,175 L 284,178" fill="none" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round"/>

  <!-- Warm Genuine Smile (Mori = Smile in Persian!) -->
  <path d="M 255,195 Q 280,215 305,195" fill="none" stroke="#f8fafc" stroke-width="2.8" stroke-linecap="round"/>
  <!-- Teeth / Smile depth -->
  <path d="M 260,195 Q 280,208 300,195 Q 280,202 260,195 Z" fill="#ffffff" opacity="0.9"/>

  <!-- Neat Trimmed Beard & Mustache -->
  <path d="M 245,185 C 260,180 300,180 315,185 C 310,192 300,195 280,195 C 260,195 250,192 245,185 Z" fill="#0a0c0f"/>
  <path d="M 215,170 C 215,225 240,265 280,268 C 320,265 345,225 345,170 C 340,210 320,245 280,248 C 240,245 220,210 215,170 Z" 
        fill="#0a0c0f" stroke="#334155" stroke-width="1.8"/>

  <!-- Forearms / Hands Leaning Forward (Conversational stance) -->
  <g stroke="#3b4454" stroke-width="2" fill="#181c24">
    <!-- Left Forearm -->
    <path d="M 120,440 C 150,460 200,500 240,540 C 210,560 170,540 140,500 Z"/>
    <!-- Right Forearm -->
    <path d="M 440,440 C 410,460 360,500 320,540 C 350,560 390,540 420,500 Z"/>
  </g>

  <!-- Accent coaching quote badge outline -->
  <path d="M 200,600 L 360,600" stroke="#0abab5" stroke-width="3" stroke-linecap="round"/>
</svg>''')

# 5. Client Sara (Section G - Gallery Card 1)
with open(os.path.join(images_dir, "gallery-client-sara.svg"), "w", encoding="utf-8") as f:
    f.write('''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 620" width="100%" height="100%" preserveAspectRatio="xMidYMid meet">
  <defs>
    <linearGradient id="saraBg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#181b22"/>
      <stop offset="100%" stop-color="#0c0d10"/>
    </linearGradient>
  </defs>

  <rect width="500" height="620" fill="url(#saraBg)"/>

  <!-- Subtle ring light circle -->
  <circle cx="250" cy="280" r="220" fill="none" stroke="#0abab5" stroke-width="1.5" opacity="0.3" stroke-dasharray="8 6"/>

  <!-- Athletic Female Boxer (Sara) in sharp guard with gloves raised -->
  <!-- Ponytail hair flowing back -->
  <path d="M 230,120 C 170,120 140,170 120,230 C 110,250 125,260 135,245 C 160,200 180,165 230,150 Z" 
        fill="#0f1115" stroke="#475569" stroke-width="1.5"/>

  <!-- Head & Hair Base -->
  <path d="M 200,140 C 200,95 240,75 275,75 C 310,75 340,95 340,140 C 340,190 310,225 275,225 C 240,225 200,190 200,140 Z" 
        fill="#232731" stroke="#3b4252" stroke-width="2"/>
  <!-- Hair line pulled into high athletic bun / ponytail -->
  <path d="M 210,125 C 220,90 250,75 285,80 C 315,85 335,105 330,130 C 310,110 270,105 230,115 Z" 
        fill="#0c0d10" stroke="#334155" stroke-width="1.8"/>

  <!-- Focused Athletic Eyes -->
  <path d="M 240,140 Q 255,136 268,140" stroke="#f8fafc" stroke-width="2" stroke-linecap="round" fill="none"/>
  <ellipse cx="255" cy="146" rx="4.5" ry="3" fill="#f8fafc"/>
  <circle cx="256" cy="146" r="2" fill="#0abab5"/>
  <path d="M 292,140 Q 305,136 318,140" stroke="#f8fafc" stroke-width="2" stroke-linecap="round" fill="none"/>
  <ellipse cx="305" cy="146" rx="4.5" ry="3" fill="#f8fafc"/>
  <circle cx="306" cy="146" r="2" fill="#0abab5"/>

  <!-- Determined smile / grit -->
  <path d="M 265,185 Q 280,192 295,185" fill="none" stroke="#f8fafc" stroke-width="2" stroke-linecap="round"/>

  <!-- Athletic Tank Top & Toned Shoulders -->
  <path d="M 180,270 C 150,330 140,410 130,500 L 410,500 C 400,410 390,330 360,270 C 320,290 220,290 180,270 Z" 
        fill="#14171d" stroke="#28303d" stroke-width="2"/>
  <path d="M 220,275 C 250,295 290,295 320,275" fill="none" stroke="#0abab5" stroke-width="2"/>

  <!-- High Boxing Guard: Left Glove Forward -->
  <!-- Left Forearm -->
  <path d="M 160,340 C 180,310 200,260 215,220" stroke="#e2e8f0" stroke-width="14" stroke-linecap="round"/>
  <!-- Left Boxing Glove (Red/Cyan accents) -->
  <path d="M 190,180 C 215,160 250,170 260,195 C 270,220 255,245 230,250 C 205,250 185,230 180,205 Z" 
        fill="#0f1115" stroke="#0abab5" stroke-width="3"/>
  <path d="M 210,185 Q 240,190 245,215" fill="none" stroke="#ffffff" stroke-width="2"/>

  <!-- Right Boxing Glove (Guarding Chin) -->
  <path d="M 330,340 C 320,310 310,260 300,220" stroke="#e2e8f0" stroke-width="14" stroke-linecap="round"/>
  <path d="M 285,185 C 310,165 345,175 355,200 C 365,225 350,250 325,255 C 300,255 280,235 275,210 Z" 
        fill="#0f1115" stroke="#e2e8f0" stroke-width="2.5"/>

  <!-- Lower artistic quote overlay area -->
  <rect x="30" y="520" width="440" height="75" rx="10" fill="#181c24" opacity="0.9" stroke="#2e3846" stroke-width="1"/>
  <text x="50" y="555" fill="#ffffff" font-family="'Albert Sans', sans-serif" font-weight="700" font-size="20">SARA</text>
  <text x="50" y="578" fill="#0abab5" font-family="'Albert Sans', sans-serif" font-weight="500" font-size="13">BOXING &amp; AGILITY · 10 WEEKS</text>
</svg>''')

# 6. Client Megan (Section G - Gallery Card 2)
with open(os.path.join(images_dir, "gallery-client-megan.svg"), "w", encoding="utf-8") as f:
    f.write('''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 620" width="100%" height="100%" preserveAspectRatio="xMidYMid meet">
  <defs>
    <linearGradient id="meganBg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#181b22"/>
      <stop offset="100%" stop-color="#0c0d10"/>
    </linearGradient>
  </defs>

  <rect width="500" height="620" fill="url(#meganBg)"/>
  <circle cx="250" cy="280" r="220" fill="none" stroke="#0abab5" stroke-width="1.5" opacity="0.3" stroke-dasharray="8 6"/>

  <!-- Athletic Female Client (Megan) in fit strength posture (lean back/shoulder tone) -->
  <!-- Athletic Bra / Top and Muscular Back & Shoulder Definition -->
  <path d="M 170,220 C 190,190 220,180 250,180 C 280,180 310,190 330,220 C 350,260 360,330 350,420 C 330,470 290,500 250,500 C 210,500 170,470 150,420 C 140,330 150,260 170,220 Z" 
        fill="#14171d" stroke="#28303d" stroke-width="2"/>

  <!-- Athletic sports top straps -->
  <path d="M 210,210 L 250,260 L 290,210" fill="none" stroke="#0abab5" stroke-width="3" stroke-linecap="round"/>
  <path d="M 180,270 L 320,270" stroke="#0abab5" stroke-width="4"/>

  <!-- Toned Deltoids & Spine Muscle Definition -->
  <g stroke="#3e4a5c" stroke-width="2" fill="none" opacity="0.8">
    <path d="M 250,270 L 250,410"/> <!-- Spine -->
    <path d="M 210,240 C 225,270 235,310 240,350"/> <!-- Left Lat -->
    <path d="M 290,240 C 275,270 265,310 260,350"/> <!-- Right Lat -->
  </g>

  <!-- Head turned in confident profile / three-quarter view -->
  <path d="M 215,140 C 215,95 245,75 275,75 C 305,75 330,95 330,140 C 330,180 305,210 275,210 C 245,210 215,180 215,140 Z" 
        fill="#232731" stroke="#3b4252" stroke-width="2"/>
  <!-- Athletic Cap / Visor (As seen in Megan's sketch!) -->
  <path d="M 220,110 C 240,95 280,95 315,105 L 345,108 C 355,108 355,115 340,118 L 310,120 Z" 
        fill="#0c0d10" stroke="#475569" stroke-width="1.8"/>

  <!-- Lower artistic quote overlay area -->
  <rect x="30" y="520" width="440" height="75" rx="10" fill="#181c24" opacity="0.9" stroke="#2e3846" stroke-width="1"/>
  <text x="50" y="555" fill="#ffffff" font-family="'Albert Sans', sans-serif" font-weight="700" font-size="20">MEGAN</text>
  <text x="50" y="578" fill="#0abab5" font-family="'Albert Sans', sans-serif" font-weight="500" font-size="13">FAT LOSS &amp; STRENGTH · 12 WEEKS</text>
</svg>''')

print("All 6 SVG illustrations generated successfully in assets/images/!")
