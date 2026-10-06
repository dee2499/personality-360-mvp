# Change Quo — Technical Architecture & Product Documentation

Change Quo is a **B2B 360-degree organizational assessment and Change Intelligence (CQ) platform**. It measures individual adaptability and collective team synchronization to help organizations see, agree, and act on change together.

---

## 1. Product Overview & Problem Statement

Traditional corporate 360-degree review tools collect backward-looking performance ratings. Change Quo measures **forward-looking Change Readiness and Adaptability** across two tiers:

1. **Individual CQ (Change Quotient)**: Measures how well team members perceive, plan, and execute change, contrasting **self-perception** against **peer consensus** to identify blind spots and hidden strengths.
2. **Team CQ Sync (Cohort Intelligence)**: Measures organizational alignment across three foundational pillars: **Seeing Together**, **Agreeing Together**, and **Acting Together**.

The platform turns qualitative survey responses into quantifiable metrics, visual radar charts, benchmark meters, gap analyses, and strategic 4-quadrant position matrices.

---

## 2. Technology Stack

### Backend
- **Framework**: **Laravel 12 / 13 (PHP 8.3)**
  - RESTful routing, Form Requests, Model Observers, and Notifications.
  - Role-based middleware (`EnsureUserIsAdmin`, `EnsureUserIsAdminOrManager`).
  - PHP 8 constructor property promotion, strict typing, and Eloquent ORM.
- **Database**:
  - **Local Development**: MySQL (via MAMP/local socket) and SQLite in-memory for testing.
  - **Production**: **Aiven Cloud MySQL 8** with mandatory SSL (`ssl-mode=REQUIRED`).
  - Session & Cache: Database-backed with automatic fallback.

### Frontend
- **Blade Templating**: Component-driven architecture (`<x-layouts.app>`, `<x-score-meter>`, etc.).
- **Tailwind CSS v4**: Utility-first styling with custom gradients, score badges, and mobile-responsive drawer navigation.
- **Alpine.js v3**: Reactive lightweight state management (modals, score tabs, animated SVG gauge needle transitions, live search filters).
- **Lucide Icons**: Modern SVG icon set across the interface.
- **Vite 8**: Modern frontend asset bundling and hot module replacement.

### Infrastructure & DevOps
- **Containerization**: Multi-stage **Docker** (Node.js 20 build stage $\rightarrow$ PHP 8.3 Apache runtime).
- **Hosting / PaaS**: **Render** (`docker-entrypoint.sh` handles auto-migration, idempotent seeding, permission setups, and health checks on port 10000).
- **Code Quality & Testing**:
  - **Laravel Pint**: Automated PSR-12 code styling.
  - **PHPUnit 12**: 95+ automated tests covering scoring calculations, authorization boundaries, and company isolation.

---

## 3. User Roles & Multi-Tenant Access Model

The platform enforces a **three-tier multi-tenant role model**:

```
                 ┌─────────────────────────────────┐
                 │       Super Admin (srini)       │
                 │   - Global System Oversight     │
                 │   - Benchmark Category Settings │
                 │   - Company Management          │
                 └──────────────┬──────────────────┘
                                │
         ┌──────────────────────┴──────────────────────┐
         ▼                                             ▼
┌─────────────────────────┐               ┌─────────────────────────┐
│  Falcon Group Manager   │               │      GCODE Manager      │
│  - Falcon Surveys       │               │  - GCODE Surveys        │
│  - Falcon Team Sync     │               │  - GCODE Team Sync      │
│  - Falcon 360 Matrix    │               │  - GCODE 360 Matrix     │
└────────────┬────────────┘               └────────────┬────────────┘
             ▼                                         ▼
┌─────────────────────────┐               ┌─────────────────────────┐
│  Falcon Employees (24)  │               │   GCODE Employees (3)   │
│  - Peer Assessments     │               │  - Peer Assessments     │
│  - Personal CQ Report   │               │  - Personal CQ Report   │
└─────────────────────────┘               └─────────────────────────┘
```

1. **Super Admin** (`srini@saipio.com`):
   - Global visibility across all companies and surveys.
   - Configures benchmark score categories (`/admin/categories`).
   - Promotes or demotes company managers.
2. **Company Manager** (`srini@falcon.com`, `srini@gcode.in`):
   - **Strictly scoped to their company** (`company_id`).
   - Accesses the **Manager Console**: company live gauges, cohort distribution, position matrix, team sync maturity meters, and leadership sign-off.
   - Cannot view other companies' data or modify global benchmark categories.
3. **Participant / Employee**:
   - Accesses the **Participant Portal**.
   - Completes assigned **Self-Assessment** and **Peer Evaluations**.
   - Unlocks their personal **CQ Report** (Spider/radar chart, blind spots, strengths, and recommendations).

---

## 4. How the Assessment Engine Works

### Step 1: Survey Setup & Question Dimensions
Each Survey contains **14 standard questions**:

#### 11 Individual CQ Questions (Scale: 1 – 10)
Each question has dual phrasing: personal reflection for self-evaluations, and peer perspective for peer reviews:
1. **Awareness of Change**: Perception of shifting environment.
2. **Understanding Key Changes**: Clarity on top priorities.
3. **Choice vs Circumstance**: Proactivity vs reactivity.
4. **Control Over Change**: Perceived locus of control.
5. **Managing Change Knowledge**: Methodological understanding.
6. **Confidence in Change**: Self-efficacy and resilience.
7. **Seeking Support**: Willingness to consult mentors and colleagues.
8. **Action Planning**: Concrete action roadmapping.
9. **Implementing Plan**: Execution discipline.
10. **Seeing Results**: Value capture and milestone feedback.
11. **Supporting Others**: Helping peers through transformation.

#### 3 Group Sync Questions (Scale: 1 – 10)
1. **See Together**: Common understanding of key changes.
2. **Agree Together**: Alignment on direction and priorities.
3. **Act Together**: Shared commitment to execute together.

---

### Step 2: The $N \times N$ Matrix Generation
When a survey is published and participants are enrolled:
- For $N$ cohort participants, the engine generates:
  - **$N$ Self-Assessments** (Assessor = Subject).
  - **$N \times (N - 1)$ Peer Assessments** (Every participant evaluates every other participant).
- **Anonymity Safeguard**: Peer scores are averaged and aggregated so individual answers cannot be deanonymized.

---

### Step 3: Scoring & Categorization Algorithms

#### A. Individual CQ Scoring
1. **Raw Score**: Sum of points across questions.
2. **Percentage Score**:
   $$\text{Percentage} = \left(\frac{\text{Total Points Scored}}{\text{Max Points Possible}}\right) \times 100$$
3. **Benchmark Categories**: Maps percentages to tier badges (e.g., Apple $\ge 80\%$, Orange, Tomato, Lemon, Cucumber).
4. **Self vs. Peer Gap Analysis**:
   $$\text{Gap} = \text{Self Score} - \text{Average Peer Score}$$
   - **Blind Spot ($\text{Gap} > 0$)**: Self-rating is significantly higher than peer consensus.
   - **Hidden Strength ($\text{Gap} < 0$)**: Peers rate the individual higher than they rate themselves.

#### B. Team CQ Sync Score (Scale: 1.0 – 10.0)
Calculated as the arithmetic mean of the 3 Group Sync dimensions:
$$\text{Team CQ Sync} = \frac{\text{See Together} + \text{Agree Together} + \text{Act Together}}{3}$$

Mapped to a 5-segment needle gauge:
- **1.0 – 2.0**: Fragmented
- **2.1 – 4.0**: Exploring
- **4.1 – 6.0**: Operational
- **6.1 – 8.0**: Integrated
- **8.1 – 10.0**: High Performance (Synchronized)

---

### Step 4: Strategic Intelligence & Reports

1. **Individual 360 Profile**: Full breakdown across all 11 competency dimensions comparing Self vs Peer perceptions.
2. **Position Matrix (4-Quadrant Strategy)**: Plots individuals by Self Score (X-axis) vs Peer Score (Y-axis):
   - **Champions**: High Self, High Peer.
   - **Hidden Talents**: Low Self, High Peer.
   - **Over-Estimators**: High Self, Low Peer.
   - **Strugglers**: Low Self, Low Peer.
3. **Cohort Distribution**: Visual distribution curve showing quartile clusters, variance, and organizational median.
4. **Leadership Sign-Off**: Management review workflow to record executive notes and formally sign off on the cohort evaluation.

---

## 5. Security & Architectural Safeguards
- **Idempotent Deployments**: Seeders use `firstOrCreate` and `syncWithoutDetaching` so container redeployments on Render never overwrite or duplicate records.
- **Privacy by Design**: Peer assessments are strictly anonymized in reports and profile views.
- **Data Isolation**: Multi-tenant scoping guarantees that company managers only query and manage data belonging to their assigned company.
