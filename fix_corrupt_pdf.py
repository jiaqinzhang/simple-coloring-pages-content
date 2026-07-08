"""
One-off fix: edu-281-08_letter.pdf (page 8 of the "Weather Chart with Sun
and Rain" topic) is truncated on the server -- exactly 65536 bytes, no
%%EOF, no xref/trailer (confirmed via direct byte inspection). This isn't
a download fluke; the file itself is broken, which is why merge_pdfs.py
failed twice on this one topic. The PNG counterpart is intact, so this
regenerates a valid single-page Letter-sized PDF from it and overwrites
the corrupt file in place.
"""
import urllib.request
import img2pdf

PNG_URL = "https://simplecoloringpagesforkids.com/wp-content/uploads/2026/07/edu-281-08_letter.png"
OUT_PATH = "/var/www/html/wp-content/uploads/2026/07/edu-281-08_letter.pdf"
TMP_PNG = "/tmp/edu-281-08_letter.png"

req = urllib.request.Request(PNG_URL, headers={"User-Agent": "scp-pdf-fix/1.0"})
with urllib.request.urlopen(req, timeout=20) as resp:
    data = resp.read()
with open(TMP_PNG, "wb") as f:
    f.write(data)

# US Letter in points (8.5in x 11in).
layout = img2pdf.get_layout_fun((img2pdf.mm_to_pt(215.9), img2pdf.mm_to_pt(279.4)))
pdf_bytes = img2pdf.convert(TMP_PNG, layout_fun=layout)

with open(OUT_PATH, "wb") as f:
    f.write(pdf_bytes)

print(f"Wrote {len(pdf_bytes)} bytes to {OUT_PATH}")

# Sanity check: valid PDF must end with %%EOF.
with open(OUT_PATH, "rb") as f:
    check = f.read()
print("Ends with %%EOF:", check.rstrip().endswith(b"%%EOF"))
