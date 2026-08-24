# CAST — Capstone Assessment Studio

Students submit papers. Advisers review, comment, score, and approve — online.

## Demo logins

- Teacher: `sir@cast.test` / `password`
- Student: `student@cast.test` / `password`

## Deploy free on Render (keeps data)

Render’s free disk is wiped on restart. CAST needs a free **Neon** Postgres URL.

1. Create a database at [neon.tech](https://neon.tech) (free). Copy the connection string (`postgresql://…?sslmode=require`).
2. In Render → service **cast** → **Environment** → add `DATABASE_URL` = that string.
3. Redeploy.

Without `DATABASE_URL`, production will not start.

Online papers should use a **Google Drive** share link (Anyone with the link). Computer uploads on the free host can vanish.

[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://render.com/deploy?repo=https://github.com/Arrays1130/CAST)

Free Render sleeps after idle; first load can take ~30–60s.
