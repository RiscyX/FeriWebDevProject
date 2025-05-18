# 🛠️ Setup Guide (Windows – Git pre-commit hook + Composer)

This project includes a Git pre-commit hook that runs PHP CodeSniffer (phpcs) to ensure PSR-12 coding standard compliance.

---

## 🔹 1. Enable the `zip` extension in PHP (required for Composer)

1. Open your PHP config file (usually at `C:\xampp\php\php.ini`)
2. Find the following line:
   ```ini
   ;extension=zip
   ```
3. Remove the semicolon to enable the extension:
   ```ini
   extension=zip
   ```
4. Save the file.
5. Restart Apache via XAMPP (or restart PHP CLI if needed).

---

## 🔹 2. Install Composer dependencies

Make sure the project root contains a valid `composer.json` with `phpcs` under `"require"`.

Then run:

```bash
composer install
```

---

## 🔹 3. Set up the Git pre-commit hook

From your project root, in Git Bash:

```bash
cp hooks/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
```

Now every time you run `git commit`, the `phpcs` check will automatically run.

---

## 🔹 4. If commit is blocked due to style errors

Run this command to auto-fix fixable errors:

```bash
./vendor/bin/phpcbf --standard=PSR12 src/
```

Then re-add the fixed files and commit again.

---

## ✅ You're ready!