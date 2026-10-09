# LIVEPETAL INTERN — Complete Product & Development Plan

**Project:** Livepetal Intern  
**Tagline:** Learn. Build. Track. Grow.  
**Organization:** Livepetal Systems Limited  
**Project Type:** SIWES Innovation Project / Student Training Management & Professional Development Platform  
**Initiator:** Peace Folorunso  
**Year:** 2026  
**Primary Framework:** Laravel  
**Recommended UI:** Blade + Livewire + Tailwind CSS  
**Database:** MySQL for production; SQLite may be used for local development/testing  
**Version 1 Goal:** A production-quality MVP that Livepetal can actually use for future SIWES students.

---

## 1. Executive Summary

Livepetal Intern is a web-based SIWES management and professional development platform designed for students undergoing industrial training at Livepetal Systems Limited.

The system should solve a real continuity problem: a trainee's work, learning, activities, projects and experience should not disappear when that trainee finishes SIWES.

Instead, every trainee gets a living digital record of their training.

The platform combines:

- Student profile
- SIWES registration
- Onboarding
- Daily activity logging
- Weekly and monthly progress
- SIWES timeline
- Skills tracking
- Projects and portfolio
- Certificates/documents
- Supervisor feedback
- Announcements
- Opportunities
- Knowledge transfer
- Report Manager
- Final SIWES report generation
- Admin/supervisor management
- Public student portfolio/profile
- Notifications
- Audit history

The central product principle is:

> **Join → Learn → Work → Document → Build → Review → Generate → Showcase → Grow**

---

# 2. Product Vision

### Vision

To make Livepetal Intern a long-term digital system that helps Livepetal train, monitor, document and develop technology students while helping those students leave with credible records of what they actually learned and accomplished.

### Mission

Turn SIWES from a temporary training placement into a documented professional journey.

### Long-term outcome

A student should finish SIWES with:

1. A complete training history.
2. A professional profile.
3. A verified record of activities.
4. Documented projects.
5. A skills record.
6. Supervisor feedback.
7. A portfolio.
8. A generated SIWES report draft.
9. Evidence of participation.
10. A clearer path to future opportunities.

Livepetal should finish each cohort with:

1. A complete trainee record.
2. Better visibility into trainee progress.
3. A history of projects and contributions.
4. Reusable knowledge from previous trainees.
5. A structured onboarding process.
6. A repeatable SIWES management workflow.

---

# 3. Problem Statement

Traditional SIWES documentation often depends on scattered notebooks, paper attendance, WhatsApp messages, files on personal computers, manually written reports and memories collected near the end of the training.

This creates several problems:

- Students forget activities they performed months earlier.
- Previous trainees' knowledge is lost when they leave.
- Supervisors have limited visibility into day-to-day progress.
- Project contributions are not consistently documented.
- Student profiles are not professionally reusable.
- Attendance and activity records can become fragmented.
- SIWES reports take significant time to compile.
- Livepetal has no single source of truth for its trainee programme.
- Future trainees have no structured way to learn from previous cohorts.

Livepetal Intern addresses these problems with one centralized system.

---

# 4. Core Product Principles

## 4.1 Record reality

The system must record what actually happened. It must not fabricate activities, skills, attendance or achievements.

## 4.2 Progressive documentation

Students should document their work throughout SIWES instead of reconstructing everything at the end.

## 4.3 Evidence first

Important claims should be connected to evidence where possible:

- project
- document
- supervisor approval
- certificate
- activity log

## 4.4 Human approval

Generated reports are drafts. Students can edit them, but final reports can be reviewed/approved by authorized supervisors or administrators.

## 4.5 Privacy by default

Private student information must never automatically become public.

## 4.6 Continuity

Future students should benefit from previous students' documented knowledge without exposing private information.

## 4.7 Professional presentation

The platform should look like a real product from Livepetal, not a generic school CRUD application.

---

# 5. Product Identity

## Recommended name

### Livepetal Intern

### Tagline

**Learn. Build. Track. Grow.**

Alternative future initiative name:

**Livepetal Next**

This can become the broader Livepetal student-development initiative while Livepetal Intern remains the actual SIWES platform.

---

# 6. User Roles

## 6.1 Student

Can:

- Complete onboarding
- Manage personal profile
- View SIWES placement details
- Record daily activities
- Submit weekly summaries
- Manage skills
- Create projects
- Upload evidence/documents
- Record challenges and reflections
- View announcements
- View opportunities
- Build portfolio
- View training timeline
- Generate report drafts
- Request report review
- View supervisor feedback
- Manage privacy/public profile settings

## 6.2 Supervisor

Can:

- View assigned students
- Monitor activity
- Review weekly summaries
- Review student projects
- Approve/reject selected activities
- Add feedback
- Review report drafts
- Request corrections
- Approve final report where authorized

## 6.3 Admin / SIWES Coordinator

Can:

- Manage users
- Manage cohorts
- Manage students
- Manage supervisors
- Create placements
- Configure SIWES periods
- Manage departments/institutions
- Manage skills/categories
- Manage projects
- Manage opportunities
- Publish announcements
- View analytics
- Manage reports
- Manage platform settings
- View audit logs

## 6.4 Super Admin

Can perform all administrative operations and manage administrator permissions.

---

# 7. Main Modules

## Module A — Public Website

Public pages:

- Home
- About Livepetal Intern
- How It Works
- For Students
- For Supervisors
- Opportunities
- Public Student Profiles
- Public Projects
- FAQ
- Contact
- Login
- Student application/onboarding entry

The public website should clearly state that Livepetal Intern is an initiative/platform developed under Livepetal Systems Limited.

---

# 8. Authentication & Account System

Features:

- Login
- Logout
- Password reset
- Email verification
- Role-based access
- Session security
- Optional remember-me
- Profile completion
- Account status

Student accounts should not automatically receive administrative permissions.

Recommended roles:

- student
- supervisor
- admin
- super_admin

Use authorization policies/gates rather than scattered role checks.

---

# 9. Student Onboarding

When a student is accepted:

1. Account is created/invited.
2. Student verifies email.
3. Student completes profile.
4. Student provides institution details.
5. Student provides SIWES information.
6. Student uploads required documents.
7. Admin/supervisor verifies required information.
8. Training begins.
9. Dashboard becomes active.

Onboarding checklist:

- Profile completed
- Institution added
- Department added
- Course added
- SIWES dates added
- Supervisor assigned
- Required documents submitted
- Emergency/contact information where required
- Profile visibility selected

---

# 10. Student Profile

The profile should be divided into sections.

### Personal

- Name
- Photo
- Bio
- Contact information
- Location where appropriate

### Academic

- Institution
- Department
- Programme/course
- Level
- Matric/student number where appropriate
- Graduation year where appropriate

### SIWES

- SIWES start date
- SIWES end date
- Assigned supervisor
- Placement status
- Cohort
- Training area

### Professional

- Skills
- Projects
- Certificates
- Portfolio links
- GitHub
- LinkedIn
- Website
- Interests

### Development

- Learning goals
- Achievements
- Reflections
- Challenges
- Supervisor feedback

---

# 11. SIWES Timeline

The timeline is one of the core experiences.

Example:

Week 1
- Orientation
- Company introduction
- Development environment setup

Week 2
- HTML/CSS
- Git/GitHub
- Responsive design

Week 3
- JavaScript
- APIs
- Project assignment

Week 4
- Laravel
- Database
- Authentication

Every timeline entry should have:

- Date
- Category
- Title
- Description
- Skills used
- Project
- Evidence/attachment if available
- Status
- Supervisor review status

Timeline filters:

- All
- Activities
- Projects
- Skills
- Workshops
- Achievements
- Reflections

---

# 12. Daily Activity Log

Students can submit daily activities.

Fields:

- Date
- Start/end time where appropriate
- Activity title
- Description
- Category
- Skills used
- Project
- Learning outcome
- Challenge
- Reflection
- Evidence
- Status

Statuses:

- Draft
- Submitted
- Reviewed
- Approved
- Returned

Do not require students to write unnecessarily long entries.

Provide useful prompts such as:

> What did you work on today?

> What did you learn?

> What challenge did you encounter?

> What did you accomplish?

---

# 13. Weekly Summary

At the end of each week:

- Activities completed
- Skills practiced
- Projects worked on
- Major achievement
- Challenge
- Lesson learned
- Next week's goal

The system should prefill the summary using the student's recorded activities, but the student must review and submit it.

---

# 14. Monthly Progress

For longer SIWES placements:

- Monthly activity summary
- Skills gained
- Projects
- Major achievements
- Supervisor comments
- Student reflection

This becomes useful input for report generation.

# 17. Projects

Students can document real work.

Project fields:

- Project name
- Description
- Problem
- Solution
- Role
- Technologies
- Skills
- Start date
- End date
- Status
- Repository URL
- Live URL
- Screenshots
- Documentation
- Supervisor verification
- Public/private setting

Project status:

- Planned
- In Progress
- Completed
- Archived

---

# 18. Skills System

Skills should be structured.

Examples:

- HTML
- CSS
- JavaScript
- PHP
- Laravel
- MySQL
- Git
- UI/UX
- WordPress
- Shopify
- Digital Marketing
- Meta Ads
- Email Marketing
- Graphic Design
- Software Testing

Each skill can have:

- Name
- Category
- Description

Student skill record:

- Skill
- Level
- First practiced
- Last practiced
- Evidence
- Project
- Supervisor validation

Suggested levels:

- Beginner
- Developing
- Competent
- Advanced

Avoid claiming professional proficiency automatically based on activity count.

---

# 19. Certificates & Documents

Students can upload:

- Certificates
- Offer/placement documents
- Training evidence
- Project documentation
- Other approved SIWES documents

Each document should have:

- Type
- Title
- File
- Date
- Description
- Visibility
- Verification status

Sensitive documents should remain private.

---

# 20. Reflection & Learning Journal

Students should have a private or supervisor-visible journal.

Entry fields:

- Date
- Title
- Reflection
- What I learned
- What challenged me
- What I will improve
- Related project/skill

This is especially useful for final SIWES reporting.

---

# 21. Supervisor Feedback

Supervisors can provide:

- General feedback
- Skill feedback
- Project feedback
- Work ethic feedback
- Communication feedback
- Technical feedback
- Improvement recommendations

Use structured ratings carefully.

Example:

- Technical learning
- Initiative
- Teamwork
- Communication
- Problem solving
- Professionalism

Ratings should support comments, not replace them.

---

# 22. Opportunities

Admin can publish:

- Internships
- Jobs
- Freelance opportunities
- Scholarships
- Hackathons
- Competitions
- Training programmes
- Livepetal opportunities

Opportunity fields:

- Title
- Organization
- Description
- Type
- Location/remote
- Deadline
- External URL
- Skills
- Status

Students can:

- Save opportunity
- Mark as interested
- Open application link

Do not claim that the platform itself guarantees placement.

---

# 23. Knowledge Transfer

This is a major long-term Livepetal feature.

Students can contribute approved knowledge resources:

- Setup guides
- Tutorials
- Lessons learned
- Common errors
- Project notes
- Tool guides
- Best practices
- Useful links

Admin/supervisor approval should be required before publication.

Private company information must never be published.

The knowledge base should become a growing resource for future cohorts.

---

# 24. Public Student Portfolio

Students can optionally enable a public profile.

Public profile may show:

- Name
- Photo
- Short bio
- Institution
- Course
- Skills
- Projects
- Certificates selected for public display
- Portfolio links
- SIWES completion status
- Approved achievements

Private information must not be exposed.

Students should have a clear:

**Make profile public / Keep private**

control.

Public URLs can use:

`/students/{username}`

or:

`/portfolio/{username}`

---

# 25. Report Manager — Flagship Module

## Purpose

The Report Manager continuously builds structured SIWES report material from information already recorded in the platform.

The student should not wait until the final week.

## Report data sources

The report engine may use:

- Student profile
- Company profile
- SIWES placement
- Daily activities
- Weekly summaries
- Monthly summaries
- Projects
- Skills
- Workshops
- Certificates
- Challenges
- Reflections
- Supervisor feedback
- Approved knowledge contributions

## Report workflow

### Stage 1 — Collect

The platform continuously stores structured information.

### Stage 2 — Organize

The system groups records by date, week, month, project and category.

### Stage 3 — Generate draft

The Report Manager creates a structured draft.

### Stage 4 — Student review

The student edits and confirms accuracy.

### Stage 5 — Supervisor review

Supervisor can approve or request corrections.

### Stage 6 — Finalize

Authorized user locks the final version.

### Stage 7 — Export

Export to PDF and optionally DOCX.

---

# 26. Report Structure

The exact school/institution template should be configurable.

A default structure can include:

## Preliminary pages

- Cover page
- Certification/approval page where applicable
- Dedication
- Acknowledgement
- Table of contents
- List of figures/tables where needed

## Chapter One

- Introduction
- Objectives of SIWES
- Importance of industrial training
- Scope of training

## Chapter Two

- Organization profile
- History/background
- Organizational structure
- Departments/services
- Technology environment where appropriate

## Chapter Three

- Training activities
- Weekly/monthly activities
- Technologies learned
- Projects
- Practical experiences

## Chapter Four

- Major project
- Problem
- Analysis
- Design
- Implementation
- Testing
- Results
- Challenges
- Solutions

## Chapter Five

- Summary
- Conclusion
- Recommendations

## Appendices

- Selected screenshots
- Approved evidence
- Certificates
- Other relevant materials

Important: report templates must be configurable because different institutions can have different SIWES requirements.

---

# 27. Report Generation Rules

The system must:

- Never invent activities.
- Never invent dates.
- Never invent technologies.
- Never invent supervisor comments.
- Never invent project results.
- Clearly distinguish generated text from verified records.
- Allow student editing.
- Preserve source records.
- Track report version.
- Track who generated/edited/approved it.
- Keep an audit history.

Generated content should be based on structured source records.

---

# 28. Report Manager UI

Student sees:

### Report Progress

- Profile: Complete
- Activities: 86%
- Weekly summaries: Complete
- Projects: Complete
- Skills: 78%
- Supervisor feedback: Pending
- Report draft: Ready

Buttons:

**Generate Draft**

**Continue Editing**

**Request Review**

**View Feedback**

**Generate Final**

**Export PDF**

**Export DOCX**

---

# 29. Dashboard

## Student dashboard

Show:

- SIWES progress
- Days completed
- Days remaining
- Activities this week
- Projects
- Skills
- Report completeness
- Supervisor feedback
- Upcoming deadlines
- Opportunities
- Recent timeline activity

A useful progress calculation can be configurable and should not pretend to measure learning scientifically.

---

# 30. Supervisor Dashboard

Show:

- Assigned students
- Students needing attention
- Pending activity reviews
- Pending weekly summaries
- Report review requests
- Recent student activity
- Progress overview

---

# 31. Admin Dashboard

Show:

- Active students
- Active supervisors
- Current cohort
- Completed trainees
- Pending reviews
- Projects
- Report status
- Opportunities
- Knowledge resources
- Recent activity

Charts should be useful, not decorative.

---

# 32. Cohort Management

Admin can create:

- SIWES cohort
- Start date
- End date
- Academic year
- Institution(s)
- Status

A cohort can contain many students.

This allows future years to remain organized:

2026 Cohort
2027 Cohort
2028 Cohort
2029 Cohort

---

# 33. Institution & Academic Information

Use reusable records for:

- Institutions
- Departments
- Courses/programmes

Avoid storing every institution as free text when structured data is possible.

---

# 34. Notifications

Notifications can cover:

- Activity returned
- Weekly summary reminder
- Supervisor feedback
- Report review
- Opportunity deadline
- Announcement
- Account/invitation

Start with in-app notifications.

Email notifications can be added after the core system works.

---

# 35. Search & Filters

Admin/supervisor should be able to search:

- Student name
- Institution
- Department
- Cohort
- Supervisor
- Status
- Skill
- Project

Students can search:

- Opportunities
- Knowledge resources
- Their projects/activities

---

# 36. Audit Log

Record important administrative events:

- User created
- Role changed
- Student approved
- Activity approved
- Activity rejected
- Report generated
- Report approved
- Project changed
- Document verified
- Opportunity published

Audit records should include:

- Actor
- Action
- Target
- Timestamp
- Relevant metadata

Do not expose sensitive audit information to ordinary students.

---

# 37. Database Architecture

Recommended core tables:

- users
- student_profiles
- supervisor_profiles
- institutions
- departments
- programmes
- cohorts
- placements
- activity_logs
- weekly_summaries
- monthly_summaries
- projects
- project_skills
- skills
- student_skills
- certificates
- documents
- reflections
- supervisor_feedback
- opportunities
- saved_opportunities
- knowledge_resources
- announcements
- notifications
- reports
- report_versions
- report_sections
- report_reviews
- audit_logs

Possible supporting tables:

- roles/permissions if using a permission package
- project_members
- activity_attachments
- report_attachments
- settings

Use foreign keys, indexes and appropriate cascading rules.

---

# 38. Important Relationships

User:

- has one student profile OR supervisor profile depending on role.

Student:

- belongs to institution
- belongs to department/programme
- belongs to cohort
- has one placement
- has many activities
- has many projects
- has many skills
- has many documents
- has many reflections
- has many reports

Supervisor:

- has many assigned students.

Project:

- belongs to student
- has many skills
- can have many activities

Report:

- belongs to student
- has many versions
- has many sections
- has review history

---

# 39. Laravel Architecture

Use:

- Laravel
- Blade
- Livewire
- Tailwind CSS
- MySQL
- Laravel validation
- Policies/Gates
- Notifications
- Queues when necessary
- Storage abstraction
- Events/listeners where useful
- Jobs for heavy report generation
- Feature tests
- Unit tests for important business logic

Avoid unnecessary complexity.

Do not build a separate SPA unless there is a demonstrated need.

---

# 40. Suggested Laravel Structure

Controllers:

- Auth/
- Student/
- Supervisor/
- Admin/
- Public/

Models:

- User
- StudentProfile
- SupervisorProfile
- Institution
- Department
- Programme
- Cohort
- Placement
- ActivityLog
- WeeklySummary
- MonthlySummary
- Project
- Skill
- StudentSkill
- Certificate
- Document
- Reflection
- SupervisorFeedback
- Opportunity
- KnowledgeResource
- Announcement
- Report
- ReportVersion
- ReportSection
- ReportReview
- AuditLog

Services:

- ReportGenerationService
- StudentProgressService
- PortfolioService
- NotificationService
- FileUploadService

Jobs:

- GenerateReportJob
- SendNotificationJob

Policies:

- StudentPolicy
- ActivityLogPolicy
- ProjectPolicy
- ReportPolicy
- DocumentPolicy
- OpportunityPolicy
- KnowledgeResourcePolicy

---

# 41. UI/UX Direction

The product should feel like a premium modern technology platform.

Avoid:

- Generic Bootstrap-looking dashboards
- Excessive gradients
- Excessive cards
- Clutter
- Tiny text
- Too many colors
- Decorative charts with no purpose
- Poor mobile layout

Use:

- Spacious layouts
- Strong typography
- Clear hierarchy
- Consistent spacing
- Subtle borders
- High-quality icons
- Responsive navigation
- Meaningful empty states
- Clear status badges
- Accessible forms
- Good loading states
- Helpful success/error feedback

The Livepetal brand should remain visible without making the system look like a school portal.

---

# 42. Mobile Experience

Students may use phones frequently.

Therefore:

- Dashboard must work on mobile.
- Activity logging must be easy on mobile.
- Uploads must work from mobile.
- Timeline must be readable.
- Navigation must be responsive.
- Tables should become cards/stacked layouts where necessary.
- Report editing should remain usable on smaller screens.

---

# 43. Security

Implement:

- CSRF protection
- Authentication
- Authorization
- Policies
- Request validation
- File validation
- Secure file storage
- Rate limiting where appropriate
- Password hashing
- Email verification
- Secure session handling
- Private document access
- Safe download authorization
- Audit logging

Never expose private student documents through predictable public URLs.

---

# 44. File Upload Security

Validate:

- MIME type
- Extension
- File size
- Storage path

Do not trust the original filename.

Use generated filenames.

Keep sensitive files private.

Use authorized download endpoints.

---

# 45. Privacy

Student data is sensitive.

Default to private.

Public profile requires explicit opt-in.

Public profile should never expose:

- Phone number
- Personal email unless explicitly allowed
- Student ID
- Private documents
- Private reflections
- Supervisor private comments
- Sensitive institutional information

---

# 46. Testing Strategy

Test:

### Authentication

- login
- logout
- password reset
- role restrictions

### Students

- onboarding
- profile updates
- activity creation
- weekly summary
- projects
- skills
- documents

### Supervisors

- assignment
- review
- feedback

### Reports

- source collection
- generation
- editing
- review
- approval
- export

### Security

- unauthorized access
- cross-student access
- private files
- role escalation

### UI

- desktop
- tablet
- mobile

---

# 47. MVP Scope

The first production MVP should include:

1. Public landing page
2. Authentication
3. Role system
4. Student onboarding
5. Student profile
6. SIWES placement
7. Student dashboard
8. Activity logging
9. Timeline
10. Projects
11. Skills
12. Supervisor dashboard
13. Admin dashboard
14. Supervisor feedback
15. Report Manager
16. PDF export
17. Basic document uploads
18. Notifications
19. Public portfolio
20. Knowledge transfer
21. Audit logs

Do not build every possible future feature before validating the core workflow.

---

# 48. Phase 1 — Foundation

- Create Laravel application
- Configure environment
- Configure database
- Install frontend tooling
- Set up authentication
- Set up roles/permissions
- Establish layout
- Establish design system
- Establish navigation
- Create database migrations
- Create factories/seeders
- Create test users

Deliverable:

A secure Laravel foundation with working authentication and role-based dashboards.

---

# 49. Phase 2 — Academic & SIWES Structure

Build:

- Institutions
- Departments
- Programmes
- Cohorts
- Placements
- Student onboarding

Deliverable:

A student can be properly enrolled into a SIWES cohort.

---

# 50. Phase 3 — Student Experience

Build:

- Student profile
- Dashboard
- Timeline
- Activity logs
- Weekly summaries
- Reflections
- Skills

Deliverable:

A student can document their complete daily/weekly journey.

---

# 51. Phase 4 — Work & Portfolio

Build:

- Projects
- Project evidence
- Certificates
- Documents
- Portfolio
- Public profile

Deliverable:

A student can prove and present what they did.

---

# 52. Phase 5 — Supervisor & Admin

Build:

- Supervisor assignment
- Supervisor dashboard
- Activity review
- Feedback
- Cohort management
- Student management
- Announcements
- Analytics

Deliverable:

Livepetal can manage and monitor trainees.

---

# 53. Phase 6 — Report Manager

Build:

- Report templates
- Report sections
- Source data collection
- Draft generation
- Editing
- Versioning
- Review
- Approval
- PDF export
- DOCX export if required

Deliverable:

A student can generate a defensible SIWES report from documented training data.

---

# 54. Phase 7 — Opportunities & Knowledge Transfer

Build:

- Opportunities
- Saved opportunities
- Knowledge resources
- Approval workflow
- Search
- Categories

Deliverable:

Students can continue benefiting from the platform beyond basic SIWES tracking.

---

# 55. Phase 8 — Quality & Production

- Security audit
- Permission audit
- Mobile testing
- Accessibility review
- Database optimization
- Storage review
- Error handling
- Empty states
- Loading states
- Email configuration
- Backup strategy
- Deployment
- Production environment
- Documentation

---

# 56. Phase 9 — Handover

Create:

- README
- Installation guide
- Environment configuration guide
- Database guide
- Admin guide
- Student guide
- Supervisor guide
- Report Manager guide
- Deployment guide
- Troubleshooting guide
- Architecture documentation
- Database relationship diagram
- API documentation if APIs exist
- Future roadmap

The project should be maintainable by future SIWES students.

---

# 57. Future Roadmap

Possible later features:

- AI-assisted report summarization with strict source grounding
- Automated report completeness checks
- Email reminders
- WhatsApp notifications where legally/technically appropriate
- Mentor matching
- Job matching
- Skills assessment
- Digital certificates
- QR verification
- Student recommendation/reference system
- Company project marketplace
- Alumni network
- Mobile application
- Multi-company deployment
- Advanced analytics

These are NOT required for the first MVP.

---

# 58. Success Metrics

The system should eventually measure:

- Number of students onboarded
- Activity completion rate
- Weekly summary completion
- Projects documented
- Skills recorded
- Supervisor review rate
- Report completion rate
- Report review turnaround
- Public portfolios created
- Knowledge resources contributed
- Opportunities viewed/saved
- Students completing SIWES with complete profiles

Avoid vanity metrics.

---

# 59. Definition of Done

The MVP is not finished merely because the pages exist.

It is finished when:

- A student can register/onboard.
- Admin can approve/manage the student.
- Supervisor can be assigned.
- Student can log daily work.
- Activities appear in the timeline.
- Student can record projects and skills.
- Supervisor can review and comment.
- Student can build a portfolio.
- Student can request a report.
- Report Manager generates content from actual stored data.
- Student can edit the draft.
- Supervisor can review/approve.
- Final report can be exported.
- Private data remains protected.
- Public profile works only when enabled.
- Mobile layout works.
- Tests pass.
- Production deployment works.
- Documentation is complete.

---

# 60. Final Product Statement

Livepetal Intern is not simply an attendance system and not simply a report generator.

It is a digital record of the student's professional journey.

The platform should help Livepetal answer:

> Who are our trainees?
>
> What are they learning?
>
> What are they working on?
>
> What have they accomplished?
>
> How are they progressing?
>
> What did each trainee contribute?
>
> What knowledge can the next cohort inherit?

And it should help the student answer:

> What did I do?
>
> What did I learn?
>
> What can I prove?
>
> What projects have I completed?
>
> What skills have I developed?
>
> What can I show an employer?
>
> What should go into my SIWES report?
>
> What opportunities should I pursue next?

---

# 61. Handover Credit

Include a subtle About/Documentation statement:

> **Livepetal Intern was initiated and developed as a SIWES innovation project at Livepetal Systems Limited, with the goal of creating a sustainable digital system that future trainees can continue to improve.**

The platform should make it easy for future contributors to continue development without claiming that they created the original system.

---

# 62. Final Recommended Architecture

Public Website
        ↓
Authentication
        ↓
Role-based Application
        ├── Student
        │   ├── Dashboard
        │   ├── Profile
        │   ├── Timeline
        │   ├── Activities
        │   ├── Projects
        │   ├── Skills
        │   ├── Reflections
        │   ├── Documents
        │   ├── Portfolio
        │   ├── Opportunities
        │   └── Report Manager
        │
        ├── Supervisor
        │   ├── Students
        │   ├── Reviews
        │   ├── Feedback
        │   └── Reports
        │
        └── Admin
            ├── Users
            ├── Cohorts
            ├── Placements
            ├── Students
            ├── Supervisors
            ├── Opportunities
            ├── Knowledge
            ├── Reports
            ├── Analytics
            └── Audit Logs

Database
        ↓
Reports / Files / Notifications / Audit Trail
