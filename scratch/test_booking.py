import urllib.request, urllib.parse, re

# Fetch page to get CSRF token and session cookie
req = urllib.request.Request('http://localhost:8080/en/')
with urllib.request.urlopen(req) as resp:
    cookies = resp.headers.get('Set-Cookie', '')
    html = resp.read().decode('utf-8')

token_match = re.search(r'name="form_token"\s+value="([^"]+)"', html)
token = token_match.group(1) if token_match else ''
print('CSRF token found:', token[:10] + '...')

# Test booking request
data = urllib.parse.urlencode({
    'action': 'book_consultation',
    'form_token': token,
    'consultation_date': '2026-10-25',
    'consultation_slot': '11:00 AM',
    'name': 'Test Consultation Client',
    'email': 'client.test@example.com',
    'phone': '+60 12-999 8888',
    'goal': 'Boxing Technique'
}).encode('utf-8')

req = urllib.request.Request('http://localhost:8080/en/', data=data, headers={'Cookie': cookies})
with urllib.request.urlopen(req) as post_resp:
    post_html = post_resp.read().decode('utf-8')
    if 'Your consultation request has been submitted' in post_html:
        print('BOOKING SUBMISSION SUCCESS: Form submitted and confirmed!')
    else:
        print('Booking result: confirmation not found')
