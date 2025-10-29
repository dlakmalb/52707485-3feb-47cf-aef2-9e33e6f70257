<div align="center">
    <h1>
        🧠 Assessment Reporting System<br/>
        <sub><sup><sub>A simple CLI-based Laravel application for generating student assessment reports.</sub></sup></sub><br/>
    </h1>
</div>
<br/>

The system produces three types of reports using JSON input data: </p>
&nbsp;&nbsp;&nbsp; 1️⃣ &nbsp; <b>Diagnostic Report</b> - identifies strengths and weaknesses by strand<br/>
&nbsp;&nbsp;&nbsp; 2️⃣ &nbsp; <b>Progress Report</b> – shows improvement over time<br/>
&nbsp;&nbsp;&nbsp; 3️⃣ &nbsp; <b>Feedback Report</b> – gives detailed feedback for incorrect answers<br/>

## 🚀 Overview

This project demonstrates:

* ✅ Clean, maintainable Laravel 12 code
* 🧩 Use of the Strategy Pattern for extensibility
* 🗂️ JSON-based in-memory data (no database)
* 🧪 Automated testing with Pest
* ⚙️ Continuous Integration via GitHub Actions
* 🐳 Optional setup via Docker Compose<br/>

## 🏗️ Architecture
```
app/
 ├── Console/Commands/GenerateReportCommand.php       # CLI entry point
 ├── Reports/
 │    ├── ReportStrategy.php                          # Strategy interface
 │    ├── DiagnosticReport.php                        # Strategy 1
 │    ├── ProgressReport.php                          # Strategy 2
 │    └── FeedbackReport.php                          # Strategy 3
 ├── Services/
 │    ├── DataLoader.php                              # Loads JSON files
 │    └── DateFormatter.php                           # Formats dates
data/
 ├── students.json
 ├── questions.json
 ├── assessments.json
 └── student-responses.json
deployment/
 ├── Dockerfile
 └── docker-compose.yml
tests/
 ├── Feature/Reports/…                                # Pest feature tests
 └── Unit/DateFormatterTest.php
```

## 🧩 Design Pattern
The app uses the Strategy Pattern, allowing new report types to be added easily
without modifying existing logic. Each report implements the `ReportStrategy` interface
and is resolved dynamically via the CLI command.

## 🧰 Technology Stack
<p align="left">
  <a href="https://skillicons.dev">
    <img src="https://skillicons.dev/icons?i=php,laravel,githubactions,docker" />
  </a>
</p>

## ⚙️ Manual Setup (Local)
1. Clone the repository<br/>
```
git clone https://github.com/dlakmalb/52707485-3feb-47cf-aef2-9e33e6f70257.git
cd 52707485-3feb-47cf-aef2-9e33e6f70257
```
2. Install dependencies<br/>
```
composer install
```
3. Environment setup<br/>
```
cp .env.example .env
php artisan key:generate
```
4. Run tests<br/>
```
vendor/bin/pest
```
5. Generate reports <br/>

Run the Artisan command directly<br/>
```
# Diagnostic report
php artisan reports:generate student1 1

# Progress report
php artisan reports:generate student1 2

# Feedback report
php artisan reports:generate student1 3
```
You can also run interactively:
```
php artisan reports:generate
> Student ID: student1
> Report to generate (1 for Diagnostic, 2 for Progress, 3 for Feedback): 1
```

## 🐳 Setup Using Docker
1. Build and start containers
```
docker compose -f deployment/docker-compose.yml build
``` 
2. Run the application
```
docker compose -f deployment/docker-compose.yml run --rm app php artisan reports:generate student1 1
```
3. Run tests
```
docker compose -f deployment/docker-compose.yml run --rm test
```

## 🧪 Automated Testing
The project uses Pest with Laravel’s TestCase.<br/>
Tests cover:
* ✅ DateFormatter service
* ✅ All three report strategies (Diagnostic, Progress, Feedback)

Common commands
```
# Run all tests
vendor/bin/pest

# Run only feature tests
vendor/bin/pest tests/Feature

# Run only unit tests
vendor/bin/pest tests/Unit

# Run only one test file
vendor/bin/pest tests/Feature/Reports/FeedbackReportTest.php

```

## 🧾 Sample Outputs
🩺 Diagnostic Report
```
Tony Stark recently completed Numeracy assessment on 16th December 2021 10:46 AM
He got 15 questions right out of 16. Details by strand given below:

Number and Algebra: 5 out of 5 correct
Measurement and Geometry: 7 out of 7 correct
Statistics and Probability: 3 out of 4 correct
```

📈 Progress Report
```
Tony Stark has completed Numeracy assessment 3 times in total. Date and raw score given below:

Date: 14th December 2019, Raw Score: 6 out of 16
Date: 14th December 2020, Raw Score: 10 out of 16
Date: 14th December 2021, Raw Score: 15 out of 16

Tony Stark got 9 more correct in the recent completed assessment than the oldest
```

💬 Feedback Report
```
Tony Stark recently completed Numeracy assessment on 16th December 2021 10:46 AM
He got 15 questions right out of 16. Feedback for wrong answers given below

Question: What is the 'median' of the following group of numbers 5, 21, 7, 18, 9?
Your answer: A with value 7
Right answer: B with value 9
Hint: You must first arrange the numbers in ascending order. The median is the middle term, which in this case is 9
```

## 🧱 CI/CD Integration
GitHub Actions pipeline automatically:
1. Installs dependencies
2. Runs Pint style checks
3. Sets up environment
4. Executes Pest tests
5. Marks PRs as ✅ Passed or ❌ Failed

Workflow: .github/workflows/ci.yml

## ⚙️ Technical Notes
* Raw scores are recomputed from student responses (not from JSON rawScore)
* All data resides in `/data`
* Dates are formatted using Carbon::translatedFormat('jS F Y h:i A') (16th December 2021, 10:46 AM)
* Only completed assessments (those with a completed date) are included in reports.
* Assumption: each student has at least one completed assessment. Incomplete assessments are ignored automatically.
