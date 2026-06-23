# 🎓 Scholarship Management System (SMS)

A centralized Web-based platform specifically architected to fully digitize the three core pillars of the scholarship lifecycle: managing application profiles, coordinating the evaluation process, and handling scholarship disbursement.

*   **Subject:** Multimedia design and web development (Class: INS306402)
*   **Lecturer:** Th.S Ta Chi Hieu
*   **Group:** Sesame

---

## 🌟 Core Functions

*   **Automated Eligibility Rules Engine:** Utilizes Backend algorithms to automatically extract applicant data, cross-reference it against configured rule sets (e.g., minimum GPA, specific certificates), and instantly issue warnings or reject unqualified applications.
*   **Scoring & Ranking Algorithm:** Provides a dedicated workspace for the evaluation committee to input detailed scores based on predefined criteria (academics, extracurriculars, etc.). The system automatically calculates weighted total scores and ranks candidates.
*   **Disbursement Log & Reporting:** Strictly monitors financial payout statuses and provides multi-dimensional statistical reports by scholarship program, academic cohort, or department.
*   **Role-Based Access Control (RBAC):** Ensures secure, isolated workflows tailored for specific user roles including Students, Reviewers, and Admins.

---

## 💻 System Architecture & Technology Stack

The system is engineered upon the MVC (Model-View-Controller) architecture combined with a robust relational database model to optimize performance and ensure data integrity. To handle heavy concurrent loads during peak submission days, the system implements the Singleton design pattern for database connections.

*   **Backend:** PHP 8.x
*   **Database:** MySQL
*   **Frontend:** HTML5, CSS3, JavaScript
*   **Async Request:** AJAX
*   **Version Control:** Git & GitHub

---

## 👥 Team Contributors (Group Sesame)

*   **Nguyen Manh Tuan (22071205):** Module 3 (Evaluation, Decision Making & Analytics) & Report.
*   **Doan Thu Duong (22070527):** Module 2 (Eligibility Rules & Applications) & Report.
*   **Nguyen Minh Tue (22070923):** Module 1 (Platform & Static Data) & Slide.