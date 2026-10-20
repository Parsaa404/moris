import urllib.request, urllib.parse, re, http.cookiejar

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

resp = opener.open('http://localhost:8080/en/')
html = resp.read().decode('utf-8')
m = re.search(r'name="form_token"\s+value="([^"]+)"', html)
if not m:
    print("No token found")
    exit(1)
token = m.group(1)
print("Token:", token[:10])

data = urllib.parse.urlencode({
    'action': 'book_consultation',
    'form_token': token,
    'consultation_date': '2026-10-25',
    'consultation_slot': '11:00 AM',
    'name': 'Test Client',
    'email': 'client@example.com',
    'phone': '+60 12-999 8888',
    'goal': 'Boxing Technique'
}).encode('utf-8')

post_resp = opener.open('http://localhost:8080/en/', data=data)
post_html = post_resp.read().decode('utf-8')

found = False
for line in post_html.splitlines():
    if 'ed-booking-feedback' in line or 'Thank you' in line:
        print("RESULT:", line.strip())
        found = True
if not found:
lines = post_html.splitlines()
for idx, line in enumerate(lines):
    if 'is-error' in line or 'is-success' in line:
        for offset in range(-1, 4):
            if 0 <= idx + offset < len(lines):
                print(f"L{idx+offset}: {lines[idx+offset].strip()}")

