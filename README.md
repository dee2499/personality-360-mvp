# 360-Degree Personality & Team CQ Assessment Platform

A comprehensive 360-degree behavioral competency, individual Change Quotient (CQ), and Team CQ Sync assessment web application built with **Laravel 11**, **PHP 8.3**, and **Tailwind CSS**.

[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://render.com/deploy?repo=https://github.com/dee2499/personality-360-mvp)

---

## 🚀 Instant Deployment on Render (100% Free)

You can deploy this application directly to Render using the button above or following these simple steps:

1. Click the **Deploy to Render** button above (or open [Render Dashboard](https://dashboard.render.com)).
2. Select **New +** > **Web Service**.
3. Connect repository: `https://github.com/dee2499/personality-360-mvp`.
4. Runtime: **Docker** (Render auto-detects `Dockerfile` and `render.yaml`).
5. Instance Type: **Free**.
6. Environment variables (already pre-configured in `render.yaml`):
   * `APP_NAME`: `Change Quo`
   * `APP_ENV`: `production`
   * `APP_DEBUG`: `false`
   * `APP_KEY`: `base64:1wWf5KHtdnNCU3beG42ECrqpZJHGTjZbPPWpYWyDkvg=`
   * `DB_CONNECTION`: `sqlite`
   * `DB_DATABASE`: `/var/www/html/database/database.sqlite`
   * `SESSION_DRIVER`: `database`
   * `CACHE_STORE`: `database`
   * `LOG_CHANNEL`: `stderr`

---

## 🔑 Default Seeded Accounts

The application automatically seeds default accounts and realistic 360 evaluation data on first launch:

| Role | Name | Email | Password | Access / Notes |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | System Admin | `admin@example.com` | `password` | Full Admin Dashboard, Companies, Surveys, Team Sync & Governance |
| **Employee (Acme Corp)** | Alex Morgan | `alex.morgan@acme.com` | `password` | Participant 360 Evaluations & Individual CQ Report |
| **Employee (Acme Corp)** | Brenda Vance | `brenda.vance@acme.com` | `password` | Participant 360 Evaluations & Individual CQ Report |
| **Employee (Acme Corp)** | Carlos Diaz | `carlos.diaz@acme.com` | `password` | Participant 360 Evaluations & Individual CQ Report |
| *(...17 more team members)* | All Acme Employees | `*@acme.com` | `password` | All 20 employees seeded with evaluations and individual reports |

---

## 🌟 Key Capabilities

1. **Individual 360° Evaluation & Moderated CQ Score**:
   - 11 behavioral competency questions with self vs peer assessment moderation.
   - Interactive Speedometer needle gauge, perception gap analysis, and 5 CQ archetypes (Resistant, Follower, Supporter, Driver, Champion).
2. **Team CQ Sync Executive Report**:
   - Measures group alignment across the 3 key dimensions: **See Together**, **Agree Together**, and **Act Together**.
   - 5-stage maturity gauge (Divergent, Fragmented, Aligned, Synchronised, Unified).
   - S-curve Team Growth Journey trajectory with milestones for Current, Next Target, and Benchmark 8.0.
   - Print-ready executive one-page layout.
3. **Team & Cohort Intelligence Hub (Admin)**:
   - Live dashboard on each company detail page (`/admin/companies/1`).
   - Direct access to Team CQ Sync Report, Position Matrix ($2 \times 2$ Capability $\times$ Synchronisation), Cohort Competency Bell Curve, and Leadership Sign-Off protocol.
