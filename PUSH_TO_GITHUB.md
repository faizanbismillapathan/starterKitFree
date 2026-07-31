# Pushing this project to your GitHub repository

The project already contains a git repository with one commit, so you only need
to add your remote and push.

## 1. Create an empty repository on GitHub

On <https://github.com/new>:

- Give it a name, for example `laravel-business-starter-kit`
- Choose **Public**
- **Do not** tick *Add a README*, *Add .gitignore* or *Choose a license*
  (the repository must be empty to avoid a merge conflict on the first push)

## 2. Extract the archive

Extract `starter-kit.zip` somewhere on your machine, for example:

```
E:\xampp-8.2\htdocs\Faizan\Laravel Starter Kit\starter-kit
```

The hidden `.git` folder is included, so the commit history comes with it.

## 3. Point it at your repository and push

Open a terminal in the project folder:

```bat
git remote add origin https://github.com/<your-username>/<your-repo>.git
git branch -M main
git push -u origin main
```

If you are asked to sign in, use a **Personal Access Token** as the password:
GitHub → Settings → Developer settings → Personal access tokens → Tokens
(classic) → Generate new token → tick the `repo` scope.

## 4. Verify

Refresh the repository page on GitHub. You should see 223 files and a single
commit titled *"feat: Laravel Business Starter Kit — Free Edition foundation"*.

---

## Working on the project afterwards

`vendor/`, `node_modules/`, `public/build/` and `.env` are intentionally not
committed, so after cloning (or extracting) run:

```bat
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Full instructions live in `SETUP.md`.

## Everyday git workflow

```bat
git add -A
git commit -m "describe what changed"
git push
```
