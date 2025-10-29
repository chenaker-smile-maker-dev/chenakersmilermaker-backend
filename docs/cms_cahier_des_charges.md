# 📋 CMS – Cahier de Charges

## Overview

The CMS manages all website content that is not related to doctor/patient reservations. This includes formations, events, testimonials, and association information. Admin has full control over these content sections.

---

## 1. Models & Data Structures

### 📚 Formation Model

Content management for educational courses and resources.

**Attributes:**

-   `id` (Primary Key)
-   `title` (string) – Formation title
-   `slug` (string, unique) – URL-friendly identifier
-   `description` (longText) – Detailed description
-   `trainer` (string) – Trainer/Instructor name
-   `duration` (integer) – Duration in minutes or hours
-   `level` (enum) – Difficulty level (Beginner, Intermediate, Advanced)
-   `status` (enum) – Visibility (Draft, Published, Archived)
-   `order` (integer) – Display order
-   `created_at` (timestamp)
-   `updated_at` (timestamp)

**Media (Spatie Media Library):**

-   Collection: `thumbnail` – Formation cover image
    -   Conversions: `thumb` (300x200), `hero` (1200x400)
-   Collection: `documents` – Associated files (PDF, Word, etc.)
-   Collection: `videos` – Video files or thumbnails

---

### 🎤 Event Model

Association events and activities showcase.

**Attributes:**

-   `id` (Primary Key)
-   `title` (string) – Event title
-   `slug` (string, unique) – URL-friendly identifier
-   `description` (longText) – Event details and description
-   `start_date` (datetime) – Event start date and time
-   `end_date` (datetime) – Event end date and time (nullable)
-   `location` (string) – Event location/venue
-   `status` (enum) – Status (Upcoming, Ongoing, Archived, Cancelled)
-   `order` (integer) – Display order
-   `created_at` (timestamp)
-   `updated_at` (timestamp)

**Media (Spatie Media Library):**

-   Collection: `poster` – Event promotional image
    -   Conversions: `thumb` (300x200), `hero` (1200x400)
-   Collection: `gallery` – Event photos and videos
    -   Conversions: `thumb` (300x300), `display` (800x600)

---

### 💬 Testimonial Model

Patient testimonials and reviews (moderated by admin).

**Attributes:**

-   `id` (Primary Key)
-   `patient_name` (string) – Testimonial author name
-   `patient_email` (string) – Author email (not public)
-   `content` (longText) – Testimonial text
-   `rating` (integer, 1-5) – Star rating
-   `doctor_id` (foreignKey, nullable) – Associated doctor
-   `is_approved` (boolean, default: false) – Admin approval status
-   `is_featured` (boolean, default: false) – Display on homepage
-   `approved_by` (foreignKey, nullable) – Admin user who approved
-   `approved_at` (timestamp, nullable)
-   `created_at` (timestamp)
-   `updated_at` (timestamp)

**Media (Spatie Media Library):**

-   Collection: `avatar` – Patient profile picture (optional)
    -   Conversions: `thumb` (80x80), `small` (150x150)

---

### 🏢 AboutClinic Model

Cabinet presentation and general information.

**Attributes:**

-   `id` (Primary Key)
-   `title` (string) – Clinic name/title
-   `mission` (longText) – Mission statement
-   `vision` (longText) – Vision statement
-   `about_description` (longText) – General about text
-   `phone` (string) – Contact phone
-   `email` (string) – Contact email
-   `address` (string) – Physical address
-   `opening_hours` (json) – Opening hours per day
    -   Structure: `{ "monday": {"open": "08:00", "close": "18:00"}, ... }`
-   `social_media` (json) – Social media links
    -   Structure: `{ "facebook": "url", "instagram": "url", ... }`
-   `seo_title` (string) – SEO page title
-   `seo_description` (string) – SEO meta description
-   `created_at` (timestamp)
-   `updated_at` (timestamp)

**Media (Spatie Media Library):**

-   Collection: `logo` – Clinic logo
    -   Conversions: `small` (150x150), `medium` (300x300)
-   Collection: `hero` – Hero banner image
    -   Conversions: `display` (1920x400)
-   Collection: `gallery` – Clinic photos
    -   Conversions: `thumb` (300x300), `display` (800x600)

---

### 🤝 Association Model

Association information and membership details.

**Attributes:**

-   `id` (Primary Key)
-   `name` (string) – Association name
-   `description` (longText) – Association description and mission
-   `founded_year` (integer, nullable) – Year founded
-   `member_count` (integer, default: 0) – Total members
-   `status` (enum) – Status (Active, Inactive)
-   `contact_email` (string) – Association contact email
-   `contact_phone` (string) – Association contact phone
-   `website_url` (string, nullable) – External website
-   `seo_title` (string) – SEO title
-   `seo_description` (string) – SEO description
-   `created_at` (timestamp)
-   `updated_at` (timestamp)

**Media (Spatie Media Library):**

-   Collection: `logo` – Association logo
    -   Conversions: `small` (150x150), `medium` (300x300)
-   Collection: `banner` – Association banner
    -   Conversions: `display` (1920x400)

---

### 📝 Page Model

Custom static pages (Terms, Privacy, etc.).

**Attributes:**

-   `id` (Primary Key)
-   `title` (string) – Page title
-   `slug` (string, unique) – URL-friendly identifier
-   `content` (longText) – Page content (HTML/Markdown)
-   `meta_title` (string) – SEO title
-   `meta_description` (string) – SEO description
-   `is_published` (boolean) – Publication status
-   `order` (integer) – Menu order (optional)
-   `created_at` (timestamp)
-   `updated_at` (timestamp)

**Media (Spatie Media Library):**

-   Collection: `featured_image` – Page thumbnail/hero image
    -   Conversions: `thumb` (300x200), `hero` (1200x400)

---

## 2. Admin Permissions & Actions

| Model           | Create | Read | Update | Delete | Special Actions         |
| --------------- | ------ | ---- | ------ | ------ | ----------------------- |
| **Formation**   | ✅     | ✅   | ✅     | ✅     | Archive, Reorder        |
| **Event**       | ✅     | ✅   | ✅     | ✅     | Archive, Change Status  |
| **Testimonial** | ✅     | ✅   | ✅     | ✅     | Approve/Reject, Feature |
| **AboutClinic** | ❌     | ✅   | ✅     | ❌     | (Single record)         |
| **Association** | ✅     | ✅   | ✅     | ✅     | Update member count     |
| **Page**        | ✅     | ✅   | ✅     | ✅     | Publish/Unpublish       |

---

## 3. Spatie Media Library Configuration

### File Storage & Conversions

All models utilize Spatie Media Library for media management:

```php
// Example: Formation Model
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Formation extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')
            ->singleFile();

        $this->addMediaCollection('documents');
        $this->addMediaCollection('videos');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(200)
            ->nonQueued();

        $this->addMediaConversion('hero')
            ->width(1200)
            ->height(400)
            ->nonQueued();
    }
}
```

### Supported File Types

| Collection                          | Allowed Types   | Max Size |
| ----------------------------------- | --------------- | -------- |
| Images (thumbnail, banner, gallery) | JPG, PNG, WebP  | 5MB      |
| Documents                           | PDF, DOCX, XLSX | 10MB     |
| Videos                              | MP4, WebM, OGV  | 50MB     |
| Avatars                             | JPG, PNG        | 2MB      |

### Media Storage Path

-   Disk: `public` (accessible via web)
-   Path: `storage/media/{model}/{collection}/`
-   URL: `/storage/media/{model}/{collection}/{filename}`

---

## 4. Frontend Display Requirements

### Formation List Page

-   Show: Title, Thumbnail (hero conversion), Duration, Level, Trainer
-   Link to: Formation detail page
-   Filters: Level, Status

### Event Timeline

-   Show: Event title, Poster (hero), Start date, Location
-   Display: Upcoming, Ongoing, Archived sections
-   Gallery: Photo gallery with thumb/display conversions

### Testimonials Widget

-   Show: Approved testimonials only
-   Display: Name, Rating, Content, Avatar (if available)
-   Featured: Display featured testimonials prominently on homepage

### Clinic Info (Footer/About)

-   Show: Logo, Address, Phone, Email
-   Social Links: Display from social_media JSON
-   Opening Hours: Display formatted schedule

---

## 5. CMS Workflow

### Content Creation

1. Admin logs into Filament admin panel
2. Navigates to respective model section (Formations, Events, etc.)
3. Fills in form with details and uploads media
4. Sets status (Draft/Published/Archived)
5. System generates media conversions automatically

### Testimonial Approval Flow

1. Patient submits testimonial via form
2. System sends admin notification
3. Admin reviews in "Pending Testimonials" section
4. Admin approves/rejects with optional feedback email
5. If approved, testimonial appears on website

### Media Management

-   Admin uploads media during content creation
-   Spatie automatically generates conversions
-   Failed conversions are logged for admin review
-   Admin can delete/replace media from content editor

---

## 6. SEO & Metadata

All content models include SEO fields:

-   `seo_title` – Custom page title (max 60 chars)
-   `seo_description` – Meta description (max 160 chars)
-   `slug` – URL-friendly identifier for canonical URLs
-   Auto-generated: `og:image` from featured media

---

## Notes

-   All timestamps use UTC
-   Soft deletes are recommended for audit trails
-   Media conversions are generated asynchronously using queues
-   Admin can preview content before publishing
