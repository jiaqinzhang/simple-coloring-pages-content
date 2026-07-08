"""
Reads /content/repo/pdf_merge_manifest.json (written by
build_pdf_merge_manifest.php), downloads each topic's individual page
PDFs, merges them into one file per topic, and writes the result to
/var/www/html/wp-content/uploads/2026/07/{slug}_all.pdf (the wp_data
volume, mounted into this container). Writes a result log to
/content/repo/pdf_merge_result.json for a follow-up WP-CLI script to
apply as post meta.
"""
import json
import os
import time
import urllib.request

from pypdf import PdfWriter

MANIFEST = "/content/repo/pdf_merge_manifest.json"
RESULT = "/content/repo/pdf_merge_result.json"
OUT_DIR = "/var/www/html/wp-content/uploads/2026/07"
TMP_DIR = "/tmp/pdfs"

os.makedirs(TMP_DIR, exist_ok=True)


def download(url, dest):
    req = urllib.request.Request(url, headers={"User-Agent": "scp-pdf-merge/1.0"})
    with urllib.request.urlopen(req, timeout=20) as resp:
        data = resp.read()
    with open(dest, "wb") as f:
        f.write(data)


def main():
    with open(MANIFEST, encoding="utf-8") as f:
        manifest = json.load(f)

    print(f"Total topics to process: {len(manifest)}")
    results = []
    ok = 0
    failed = 0

    for i, entry in enumerate(manifest, start=1):
        slug = entry["slug"]
        urls = entry["pdf_urls"]
        writer = PdfWriter()
        local_files = []
        try:
            for j, url in enumerate(urls):
                local_path = os.path.join(TMP_DIR, f"page_{j}.pdf")
                download(url, local_path)
                local_files.append(local_path)
                writer.append(local_path)

            out_path = os.path.join(OUT_DIR, f"{slug}_all.pdf")
            with open(out_path, "wb") as f:
                writer.write(f)
            writer.close()

            size_bytes = os.path.getsize(out_path)
            size_mb = round(size_bytes / 1048576, 1)
            results.append({"topic_id": entry["topic_id"], "slug": slug, "status": "ok", "size_mb": size_mb, "pages": len(urls)})
            ok += 1
        except Exception as e:
            results.append({"topic_id": entry["topic_id"], "slug": slug, "status": "error", "error": str(e)})
            failed += 1
        finally:
            for lf in local_files:
                try:
                    os.remove(lf)
                except OSError:
                    pass

        if i % 25 == 0:
            print(f"  ...{i}/{len(manifest)} processed (ok={ok}, failed={failed})")

    with open(RESULT, "w", encoding="utf-8") as f:
        json.dump(results, f, indent=2)

    print(f"Done. OK: {ok}  Failed: {failed}")


if __name__ == "__main__":
    main()
