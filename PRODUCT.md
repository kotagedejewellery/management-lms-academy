# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

Delegated and accepted: Laravel 13 with PHP 8.4+, Inertia.js 3, Vue 3 with TypeScript, Tailwind CSS 4, PostgreSQL, Redis, private S3-compatible storage, and Docker for local development.

## Users

Employees learn assigned material and complete Pre-Test and Post-Test. LMS Admin/HR manages people, content, paths, assignments, and reports. Trainers manage assigned courses and assessments. Managers monitor only their authorized teams. Management views aggregate reports.

## Product Purpose

KGJ Academy is Kotagede Jewellery's internal learning management system for onboarding, SOPs, product knowledge, job-specific competency, evaluation, and organization-wide progress reporting.

## Positioning

An administrator-configurable learning flow maps employees to division, position, branch, and learning path without code changes, then records Pre-Test, learning completion, Post-Test, and auditable reporting in one system.

## Operating Context

The product is used on desktop and mobile web by internal employees across divisions and branches. Courses contain video, articles, PDFs, and resources. Assessments, completion, access control, exports, and audit records are core operating workflows.

## Capabilities and Constraints

Pre-Test and Post-Test are distinct assessment types; all attempts and responses are retained. Post-Test access requires required lessons to be completed. A course completes only after required lessons and a passing Post-Test. Admin builds material while a course is Draft: articles require text, PDF/video require one valid private file or URL source, and Canva presentations require a view-only or embed link. Modules, lessons, assessment questions, and course entries in a learning path are appended automatically; Admin reorders them with explicit move controls. Published course content is read-only until the course is returned to Draft. Form feedback is actionable: validation is in Indonesian, the first invalid field receives focus, request state is visible, and upload flows expose progress. Phase 1 supports manual employee input and Admin-configurable passing grades per course/assessment. Attempt limits, retake rules, course materials per division, production hosting, and certificate rollout remain process-owner decisions.

## Brand Commitments

Use the name KGJ Academy / Kotagede Jewellery. The supplied visual board is binding direction: black and white/off-white base, restrained gold accents, modern typography, clean cards, generous negative space, desktop sidebar, and a mobile-adapted navigation.

## Evidence on Hand

`KGJ_Academy_LMS_Brief_dan_Visual.pdf` supplied by the user is the source of product requirements and visual reference. Its mock data, names, scores, dates, and metric values are illustrative and must not be treated as production facts.

## Product Principles

1. Keep the learning flow explicit: assignment, baseline, learning, evaluation, outcome.
2. Enforce least-privilege access and preserve auditability.
3. Make frequent learner tasks fast on mobile and dense administration tasks clear on desktop.
4. Let administrators configure organizational learning without code changes.
5. Optimize only measured or known hot paths; defer infrastructure that the MVP does not need.

## Accessibility & Inclusion

Responsive web UI must remain usable on mobile, support keyboard navigation, visible focus, sufficient color contrast, semantic form controls, and non-color status indicators.
