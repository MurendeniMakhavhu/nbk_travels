\# NBK Travel — Shuttle Booking Management System



A web-based shuttle booking, verification, and dispatch platform built

for NBK Travel, a South African shuttle and transport service company.

This repository contains the individually-implemented PHP/MySQL web

application component of the broader NBK Travel Shuttle Booking

Management System (Diploma in IT Software Development — Work

Integrated Learning, XISD5319/XISD6329).



\## Client Problem \& Proposed Solution



NBK Travel previously managed all bookings through telephone calls,

paper registries, and personal spreadsheets — an approach prone to

double-bookings, lost records, and no reliable way to verify who a

paying customer actually is before assigning a driver and vehicle to

their trip.



This system replaces that process with a web application that:

\- Lets prospective customers \*\*register\*\* an account online instead of

&#x20; phoning in

\- Requires \*\*administrator verification\*\* before a new registration can

&#x20; log in and book, giving NBK Travel control over who is treated as a

&#x20; paying customer

\- Lets verified customers \*\*submit and track their own bookings\*\*

\- Gives administrators a single dashboard to \*\*verify registrations\*\*,

&#x20; manage \*\*customer, driver, and vehicle records\*\*, and \*\*assign\*\*

&#x20; drivers/vehicles to pending bookings, which automatically creates a

&#x20; trip \*\*schedule\*\*



\## Scope



\*\*In scope (implemented):\*\*

\- Customer registration, login, and admin-gated verification

\- Admin CRUD for customers, drivers, and vehicles

\- Customer booking creation and personal booking history

\- Admin view of all bookings with driver/vehicle assignment

\- Automatic schedule creation on assignment



\*\*Out of scope for this iteration\*\* (see known limitations below):

\- Invoice generation

\- Automated customer/driver notifications

\- Native mobile application (planned as a separate module — see Team

&#x20; \& Roles)



\## Team \& Roles



| Name | Role |

|---|---|

| Murendeni Makhavhu | Project Manager / Database Administrator — this repository |

| Shenice Wood | Mobile App (Kotlin, Android Studio, Room Database) |

| Murendeni Makhavhu | Database Administrator — this repository |

| Thandiwe Sibeko | System Analyst |

| Matome Maopye | UI/UX Designer \& QA Tester |

| Mzamo Richmond Ndlovu | Software Developer |



\## Tools \& Technologies



\- \*\*Backend:\*\* PHP 8.3, MySQLi with prepared statements throughout

\- \*\*Database:\*\* MySQL (Community Edition), managed via phpMyAdmin

\- \*\*Local environment:\*\* WAMP (Apache + MySQL + PHP for Windows)

\- \*\*Version control:\*\* Git \& GitHub

\- \*\*Frontend:\*\* HTML,  CSS 



\## How to Run This Prototype



1\. Install \[WAMP](https://www.wampserver.com/) (or an equivalent

&#x20;  Apache/MySQL/PHP stack).

2\. Clone this repository into your WAMP `www` directory:

&#x20;  ```

&#x20;  git clone https://github.com/MurendeniMakhavhu/nbk\_travels.git

&#x20;  ```

3\. Open phpMyAdmin and import the schema at `sql/nbk\_travel\_schema.sql`.

4\. Confirm `config/db.php` matches your local MySQL credentials

&#x20;  (defaults are correct for a standard WAMP install: host

&#x20;  `localhost`, user `root`, empty password).

5\. Start WAMP and visit `http://localhost/nbk\_travels/login.php`.



\## Folder Structure



```

nbk\_travels/

├── login.php                 # customer login (landing page)

├── register.php               # customer self-registration

├── dashboard.php               # logged-in customer landing page

├── book.php                    # customer: create \& view own bookings

├── logout.php

├── admin\_login.php             # administrator login

├── admin\_dashboard.php         # verify pending registrations

├── admin\_customers.php         # admin CRUD: customers

├── admin\_drivers.php           # admin CRUD: drivers

├── admin\_vehicles.php          # admin CRUD: vehicles

├── admin\_bookings.php          # admin: view all bookings, assign driver/vehicle

├── admin\_logout.php

├── config/

│   └── db.php                  # database connection

└── sql/

&#x20;   └── nbk\_travel\_schema.sql   # full database schema export

```



\## Links



\- \*\*GitHub repository:\*\* https://github.com/MurendeniMakhavhu/nbk\_travels

\- \*\*Live prototype link:\*\* \_pending — currently runs on local WAMP only; hosted link to be added\_



\## Current Prototype Status \& Known Limitations



\- All core functionality listed under "Scope" above is implemented and

&#x20; manually tested (registration → admin verification → booking →

&#x20; driver/vehicle assignment → automatic scheduling).

\- \*\*Storage engine limitation:\*\* database tables currently use MyISAM

&#x20; (phpMyAdmin's default), which does not enforce foreign key

&#x20; constraints. Referential integrity is currently maintained at the

&#x20; application layer only. See `sql/nbk\_travel\_schema.sql` for full

&#x20; detail and the planned remediation (migrating to InnoDB).

\- Pages currently use plain HTML tables/forms rather than semantic

&#x20; HTML5 structure (`<header>`, `<nav>`, `<main>`, `<footer>`) or a

&#x20; polished visual design — a presentation/accessibility pass is

&#x20; planned.

\- No automated test suite yet; testing to date has been manual,

&#x20; scenario-based testing performed while building each feature.



\## Testing



Manual functional testing was performed continuously during

development, covering: successful and failed login attempts, sticky

form behaviour on failed login, registration validation, admin

verification creating a linked customer record, full CRUD on

customers/drivers/vehicles, booking creation and retrieval scoped to

the logged-in customer only, and driver/vehicle assignment correctly

creating a schedule entry. See commit history for incremental feature

delivery and bug fixes discovered during this testing process.



\## References \& Attribution



\- Password hashing: PHP native `password\_hash()` / `password\_verify()`

&#x20; functions (bcrypt, `PASSWORD\_DEFAULT` algorithm)

\- Database design informed by Connolly and Begg (2015), \*Database

&#x20; Systems: A Practical Approach to Design, Implementation and

&#x20; Management\*

