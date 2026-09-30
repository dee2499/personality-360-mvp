# 360-Degree Personality Assessment MVP

A comprehensive 360-degree personality and self-assessment web application built with **Laravel 11**, **PHP 8.3**, and **Tailwind CSS**.

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
   * `APP_NAME`: `Personality 360`
   * `APP_ENV`: `production`
   * `APP_DEBUG`: `false`
   * `APP_KEY`: `base64:1wWf5KHtdnNCU3beG42ECrqpZJHGTjZbPPWpYWyDkvg=`
   * `DB_CONNECTION`: `sqlite`

---

## 🔑 Default Seeded Accounts

The application automatically seeds an MVP survey and test users upon initial container launch:

| Role | Name | Email | Password |
| :--- | :--- | :--- | :--- |
| **Admin** | Administrator | `admin@example.com` | `password` |
| **Participant** | Dipak | `dipak@example.com` | `password` |
| **Participant** | Vishy | `vishy@example.com` | `password` |
| **Participant** | Srini | `srini@example.com` | `password` |

---

## 📊 Score Calculation & Calibration Scale

* **Score Formula**:
  $$\text{Percentage} = \frac{\sum \text{Assigned Scores}}{\sum \text{Max Possible Scores}} \times 100\%$$
* **Benchmark Categories**:
  * 🍏 **Apple**: 0% – 20%
  * 🍊 **Orange**: >20% – 40%
  * 🍅 **Tomato**: >40% – 60%
  * 🍋 **Lemon**: >60% – 80%
  * 🥒 **Cucumber**: >80% – 100%
