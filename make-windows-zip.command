#!/bin/bash
ROOT="$(cd "$(dirname "$0")" && pwd)"
NAME="$(basename "$ROOT")"
PARENT="$(dirname "$ROOT")"
OUT="$HOME/Desktop/Moris-Website-For-Windows.zip"
rm -f "$OUT"
cd "$PARENT"
zip -r -X "$OUT" "$NAME" \
  -x "$NAME/.git/*" \
  -x "$NAME/includes/config.php" \
  -x "$NAME/includes/.installed" \
  -x "$NAME/assets/images/uploads/*" \
  -x "$NAME/**/.DS_Store"
echo "Created: $OUT"
open -R "$OUT"
read -r -p "Press Enter to close..."
