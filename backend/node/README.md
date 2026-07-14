# Talentteno Node Backend

Node.js API for the existing MySQL database used by the PHP admin panel.

## Setup

```bash
cd backend/node
cp .env.example .env
npm install
npm run dev
```

Import `database/database_setup.sql` before starting the server.

## Main Endpoints

- `GET /api/health`
- `GET /api/settings`
- `GET /api/company-profile`
- `GET /api/seo`
- `GET /api/seo?page=home|about|courses|contact`
- `GET /api/courses`
- `GET /api/courses?type=course`
- `GET /api/courses?type=short`
- `GET /api/courses?type=popular`
- `GET /api/courses?type=advanced`
- `GET /api/courses?featured=1`

Course responses include normalized `highlights` as an array and `fee_label` for frontend display.
- `GET /api/services`
- `GET /api/process-steps`
- `GET /api/testimonials`
- `POST /api/enquiries`
- `POST /api/admin/login`

`/api/seo` returns title, description, keywords, canonical URL, Open Graph, Twitter card data, JSON-LD structured data, and company profile details for frontend rendering.
