<?php
/**
 * Admin — add or edit a package.
 * No id in the URL = create new. With id = edit existing.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();
$admin = current_admin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$isNew = ($id === 0);

// Default values for a new package.
$pkg = [
    'id' => 0,
    'name_en' => '', 'name_fa' => '',
    'description_en' => '', 'description_fa' => '',
    'features_en' => '', 'features_fa' => '',
    'price' => '', 'currency' => 'MYR',
    'sessions_count' => 10, 'session_duration_min' => 60,
    'includes_boxing' => 0, 'includes_weights' => 1, 'is_online' => 0,
    'sort_order' => 0, 'is_active' => 1,
];

if (!$isNew) {
    $stmt = db()->prepare('SELECT * FROM packages WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash_set('That package was not found.', 'error');
        redirect('admin/packages.php');
    }
    $pkg = $found;
}

$errors = [];
$currencies = ['MYR', 'USD', 'EUR'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Security token invalid. Please try again.';
    } else {
        $pkg['name_en']        = trim((string) ($_POST['name_en'] ?? ''));
        $pkg['name_fa']        = trim((string) ($_POST['name_fa'] ?? ''));
        $pkg['description_en'] = sanitize_html((string) ($_POST['description_en'] ?? ''));
        $pkg['description_fa'] = sanitize_html((string) ($_POST['description_fa'] ?? ''));
        $pkg['features_en']    = trim((string) ($_POST['features_en'] ?? ''));
        $pkg['features_fa']    = trim((string) ($_POST['features_fa'] ?? ''));
        $pkg['price']          = (string) ($_POST['price'] ?? '');
        $pkg['currency']       = in_array($_POST['currency'] ?? '', $currencies, true) ? $_POST['currency'] : 'MYR';
        $pkg['sessions_count'] = ($_POST['sessions_count'] ?? '') === '' ? null : (int) $_POST['sessions_count'];
        $pkg['session_duration_min'] = ($_POST['session_duration_min'] ?? '') === '' ? null : (int) $_POST['session_duration_min'];
        $pkg['includes_boxing']  = isset($_POST['includes_boxing']) ? 1 : 0;
        $pkg['includes_weights'] = isset($_POST['includes_weights']) ? 1 : 0;
        $pkg['is_online']        = isset($_POST['is_online']) ? 1 : 0;
        $pkg['sort_order']       = (int) ($_POST['sort_order'] ?? 0);
        $pkg['is_active']        = isset($_POST['is_active']) ? 1 : 0;

        if ($pkg['name_en'] === '') {
            $errors[] = 'English name is required.';
        }
        if ($pkg['price'] === '' || !is_numeric($pkg['price']) || (float) $pkg['price'] < 0) {
            $errors[] = 'Enter a valid price (a number, e.g. 1500).';
        }

        if (!$errors) {
            $price = (float) $pkg['price'];
            if ($isNew) {
                $sql = 'INSERT INTO packages
                        (name_en, name_fa, description_en, description_fa, features_en, features_fa,
                         price, currency, sessions_count, session_duration_min,
                         includes_boxing, includes_weights, is_online, sort_order, is_active)
                        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
                $params = [
                    $pkg['name_en'], $pkg['name_fa'], $pkg['description_en'], $pkg['description_fa'],
                    $pkg['features_en'], $pkg['features_fa'], $price, $pkg['currency'],
                    $pkg['sessions_count'], $pkg['session_duration_min'],
                    $pkg['includes_boxing'], $pkg['includes_weights'], $pkg['is_online'],
                    $pkg['sort_order'], $pkg['is_active'],
                ];
            } else {
                $sql = 'UPDATE packages SET
                        name_en=?, name_fa=?, description_en=?, description_fa=?, features_en=?, features_fa=?,
                        price=?, currency=?, sessions_count=?, session_duration_min=?,
                        includes_boxing=?, includes_weights=?, is_online=?, sort_order=?, is_active=?
                        WHERE id=?';
                $params = [
                    $pkg['name_en'], $pkg['name_fa'], $pkg['description_en'], $pkg['description_fa'],
                    $pkg['features_en'], $pkg['features_fa'], $price, $pkg['currency'],
                    $pkg['sessions_count'], $pkg['session_duration_min'],
                    $pkg['includes_boxing'], $pkg['includes_weights'], $pkg['is_online'],
                    $pkg['sort_order'], $pkg['is_active'], $id,
                ];
            }
            db()->prepare($sql)->execute($params);
            flash_set($isNew ? 'Package created.' : 'Package saved.');
            redirect('admin/packages.php');
        }
    }
}

$pageHeading = $isNew ? 'Add package' : 'Edit package';
require __DIR__ . '/_header.php';
?>

<p class="muted"><a href="<?= e(url('admin/packages.php')) ?>">← Back to packages</a></p>

<?php if ($errors): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post" class="panel form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $pkg['id'] ?>">

    <div class="lang-cols">
        <div class="lang-col">
            <h2 class="lang-title">English</h2>
            <label>Name
                <input type="text" name="name_en" value="<?= e($pkg['name_en']) ?>" required>
            </label>
            <label>Description
                <textarea name="description_en" class="rich-ltr" rows="6"><?= e($pkg['description_en']) ?></textarea>
            </label>
            <label>Features <span class="muted">(one per line)</span>
                <textarea name="features_en" rows="5" placeholder="e.g.&#10;10 personal sessions&#10;Custom program&#10;Nutrition guidance"><?= e($pkg['features_en']) ?></textarea>
            </label>
        </div>

        <div class="lang-col">
            <h2 class="lang-title">Persian (فارسی)</h2>
            <label>Name
                <input type="text" name="name_fa" value="<?= e($pkg['name_fa']) ?>" dir="rtl">
            </label>
            <label>Description
                <textarea name="description_fa" class="rich-rtl" rows="6" dir="rtl"><?= e($pkg['description_fa']) ?></textarea>
            </label>
            <label>Features <span class="muted">(هر مورد در یک خط)</span>
                <textarea name="features_fa" rows="5" dir="rtl"><?= e($pkg['features_fa']) ?></textarea>
            </label>
        </div>
    </div>

    <h2 class="lang-title">Pricing &amp; details</h2>
    <div class="field-grid">
        <label>Price
            <input type="number" name="price" value="<?= e((string) $pkg['price']) ?>" step="0.01" min="0" required placeholder="1500">
        </label>
        <label>Currency
            <select name="currency">
                <?php foreach ($currencies as $c): ?>
                    <option value="<?= e($c) ?>" <?= $pkg['currency'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Sessions
            <input type="number" name="sessions_count" value="<?= e((string) $pkg['sessions_count']) ?>" min="0" placeholder="10">
        </label>
        <label>Session length (min)
            <input type="number" name="session_duration_min" value="<?= e((string) $pkg['session_duration_min']) ?>" min="0" placeholder="60">
        </label>
        <label>Sort order
            <input type="number" name="sort_order" value="<?= e((string) $pkg['sort_order']) ?>" placeholder="0">
        </label>
    </div>

    <div class="check-row">
        <label class="checkbox"><input type="checkbox" name="includes_weights" value="1" <?= (int) $pkg['includes_weights'] ? 'checked' : '' ?>> Weight training</label>
        <label class="checkbox"><input type="checkbox" name="includes_boxing" value="1" <?= (int) $pkg['includes_boxing'] ? 'checked' : '' ?>> Boxing</label>
        <label class="checkbox"><input type="checkbox" name="is_online" value="1" <?= (int) $pkg['is_online'] ? 'checked' : '' ?>> Online</label>
        <label class="checkbox"><input type="checkbox" name="is_active" value="1" <?= (int) $pkg['is_active'] ? 'checked' : '' ?>> Show on website</label>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-inline"><?= $isNew ? 'Create package' : 'Save changes' ?></button>
        <a class="btn btn-ghost" href="<?= e(url('admin/packages.php')) ?>">Cancel</a>
    </div>
</form>

<?php require __DIR__ . '/_editor.php'; ?>
<?php require __DIR__ . '/_footer.php'; ?>
