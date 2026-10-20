"""
Render high-res athletic gallery portraits for Sara and Megan with charcoal & ink athletic styling.
"""
import os
from PIL import Image, ImageDraw, ImageFont, ImageFilter

dest_dir = r"c:\Users\vatan\Desktop\Mori\moris-website\assets\images"

def create_client_portrait(filename, name, subtitle, quote, is_boxer=True):
    w, h = 800, 1000
    im = Image.new("RGB", (w, h), (18, 21, 26))
    draw = ImageDraw.Draw(im)

    # Gradient background with circular glow
    for y in range(h):
        r = int(14 + (y / h) * 12)
        g = int(17 + (y / h) * 12)
        b = int(22 + (y / h) * 12)
        draw.line([(0, y), (w, y)], fill=(r, g, b))

    # Ring light ellipse in background
    for rad in range(320, 260, -8):
        alpha = int((320 - rad) * 0.8)
        draw.ellipse([400 - rad, 420 - rad, 400 + rad, 420 + rad], outline=(10, 186, 181, 120), width=2)

    # Athletic silhouette & sketch lines
    if is_boxer:
        # Female boxer (Sara) in guard with gloves
        # Torso
        draw.polygon([(260, 480), (540, 480), (580, 850), (220, 850)], fill=(24, 28, 35), outline=(45, 52, 65))
        # Head & Ponytail
        draw.ellipse([340, 240, 460, 380], fill=(30, 36, 45), outline=(60, 70, 85), width=2)
        draw.polygon([(340, 260), (260, 220), (280, 320), (350, 300)], fill=(15, 18, 22)) # Ponytail
        # Face profile
        draw.ellipse([385, 290, 440, 350], fill=(42, 50, 62))
        draw.ellipse([415, 305, 425, 315], fill=(240, 245, 250)) # Eye
        draw.ellipse([418, 307, 422, 313], fill=(10, 186, 181)) # Iris
        # Left Glove in front
        draw.ellipse([420, 380, 560, 520], fill=(12, 14, 18), outline=(10, 186, 181), width=4)
        draw.arc([440, 400, 540, 500], start=30, end=210, fill=(240, 245, 250), width=3)
        # Right Glove at jaw
        draw.ellipse([310, 370, 420, 480], fill=(16, 19, 24), outline=(200, 210, 220), width=3)
        # Wraps & arms
        draw.line([(360, 460), (320, 620)], fill=(200, 210, 220), width=24)
        draw.line([(480, 480), (500, 640)], fill=(200, 210, 220), width=26)
    else:
        # Athletic fitness client (Megan)
        # Muscular back & shoulders in fitness top
        draw.polygon([(240, 420), (560, 420), (520, 850), (280, 850)], fill=(24, 28, 35), outline=(45, 52, 65))
        # Head turned
        draw.ellipse([340, 230, 460, 370], fill=(30, 36, 45), outline=(60, 70, 85), width=2)
        # Athletic Cap
        draw.polygon([(340, 230), (450, 220), (510, 240), (440, 270), (350, 270)], fill=(12, 14, 18))
        # Toned back musculature lines
        draw.line([(400, 440), (400, 720)], fill=(55, 68, 85), width=3) # Spine
        draw.arc([320, 460, 480, 650], start=40, end=140, fill=(10, 186, 181), width=3) # Left lat
        draw.arc([320, 460, 480, 650], start=220, end=320, fill=(10, 186, 181), width=3) # Right lat
        # Sports bra straps
        draw.line([(320, 420), (400, 500)], fill=(10, 186, 181), width=5)
        draw.line([(480, 420), (400, 500)], fill=(10, 186, 181), width=5)

    # Dynamic action lines & crosshatching
    for x in range(100, 700, 60):
        draw.line([(x, 820), (x + 80, 920)], fill=(35, 42, 54), width=1)

    # Lower Overlay Badge Card
    draw.rounded_rectangle([40, 840, 760, 960], radius=14, fill=(10, 12, 16), outline=(40, 48, 60), width=1)
    
    # Text labels
    # Use default bitmap font with scaled drawing
    draw.text((60, 860), name, fill=(255, 255, 255))
    draw.text((60, 890), subtitle, fill=(10, 186, 181))
    draw.text((60, 920), quote, fill=(180, 190, 205))

    im.save(os.path.join(dest_dir, filename), quality=95)
    print(f"Created {filename}")

create_client_portrait("gallery-sara.jpg", "SARA — BOXING & AGILITY", "10 WEEKS PROGRAM", "Stamina and reaction speed transformed.", is_boxer=True)
create_client_portrait("gallery-megan.jpg", "MEGAN — FAT LOSS & STRENGTH", "12 WEEKS PROGRAM", "Lost 11kg and built lean, functional muscle.", is_boxer=False)
