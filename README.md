# CAST — Capstone Assessment Studio

Students submit papers. Advisers review, comment, score, and approve — online.

## Local demo logins

Only when `APP_ENV=local` (or `SEED_DEMO=true`):

- Teacher: `briel@ilinkcst.edu.ph` / `iloveyouILINK` (change password on first login)
- Student: `student@cast.test` / `password`
- Student: `student@cast.test` / `password`

Production never seeds these accounts unless you explicitly set `SEED_DEMO=true`.

## Deploy on Render

1. Create a free [Neon](https://neon.tech) Postgres database. Copy `DATABASE_URL`.
2. In Render → **cast** → **Environment**, set at least:
   - `APP_KEY` — generate with `php artisan key:generate --show` (do **not** commit this)
   - `DATABASE_URL` — Neon connection string
   - `TEACHER_INVITE_CODE` — shared secret for adviser signup (optional but recommended)
3. Recommended:
   - Mail (verify email / password reset) via **Resend** (inbox, not spam):
     - `MAIL_MAILER=resend`
     - `RESEND_API_KEY` — from [resend.com](https://resend.com) API keys
     - `MAIL_FROM_ADDRESS` — use `onboarding@resend.dev` for tests, or a domain you verified in Resend
     - `MAIL_FROM_NAME=CAST Studio`
   - Persistent uploads: Cloudflare R2 / S3 — `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_ENDPOINT`, `AWS_URL`, `AWS_USE_PATH_STYLE_ENDPOINT=true` for R2
4. Redeploy. Rotate any key or DB password that was ever pasted in chat or committed.

Without `DATABASE_URL` and `APP_KEY`, production will not start.

Online manuscripts should prefer a **Google Drive** share link if you have no S3/R2. Computer uploads on the free host vanish unless object storage is configured.

[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://render.com/deploy?repo=https://github.com/Arrays1130/CAST)

Free Render sleeps after idle; first load can take ~30–60s.

## Security notes

- Self-register is student-only unless a valid `TEACHER_INVITE_CODE` is entered
- Existing advisers can promote a student from **Settings**
- Email verification is required before using the studio
- Reference Detective flags unused bibliography entries and missing citations (heuristic)
