/**
 * ====================================================================
 * StudentHub Portal - Fallback Data Bundle
 * ====================================================================
 * Provides immediate fallback data when running directly in browser
 * via file:/// protocol where local Fetch API requests may trigger CORS security errors.
 */

(function (global) {
    'use strict';

    global.StudentHubFallbackData = {
        events: [
  {
    "id": 1,
    "title": "CodeQuest Hackathon 2026",
    "category": "Technical",
    "date": "2026-10-15",
    "time": "09:00 AM - 06:00 PM",
    "venue": "Main Auditorium & Lab Complex",
    "organizer": "Department of Computer Science & Engineering",
    "capacity": 200,
    "registered": 185,
    "fee": "Free",
    "badge": "Featured",
    "status": "Filling Fast",
    "description": "A 24-hour inter-college hackathon solving real-world challenges in AI, Web3, and Smart Campus automation with exciting cash prizes.",
    "speaker": "Dr. Rajeshwar Sharma (AI Architect)"
  },
  {
    "id": 2,
    "title": "Hands-on Workshop: Full-Stack Web Development",
    "category": "Workshop",
    "date": "2026-10-18",
    "time": "10:00 AM - 01:00 PM",
    "venue": "Computer Lab 302",
    "organizer": "Student Developer Club",
    "capacity": 80,
    "registered": 75,
    "fee": "₹100",
    "badge": "Popular",
    "status": "Filling Fast",
    "description": "Deep dive into React, Node.js, REST APIs, and asynchronous programming with live project deployment on cloud.",
    "speaker": "Prof. S. K. Gupta"
  },
  {
    "id": 3,
    "title": "Cyber Security & Ethical Hacking Symposium",
    "category": "Seminar",
    "date": "2026-10-22",
    "time": "11:00 AM - 02:00 PM",
    "venue": "Seminar Hall B",
    "organizer": "InfoSec Student Chapter",
    "capacity": 150,
    "registered": 98,
    "fee": "Free",
    "badge": "Upcoming",
    "status": "Open",
    "description": "Learn modern vulnerability assessment, network penetration testing, and zero-day defense strategies from industry red-teamers.",
    "speaker": "Vikram Seth (Security Researcher)"
  },
  {
    "id": 4,
    "title": "AI & Generative Models Masterclass",
    "category": "Technical",
    "date": "2026-10-25",
    "time": "02:00 PM - 05:00 PM",
    "venue": "Virtual / Google Meet",
    "organizer": "AI & Data Science Department",
    "capacity": 300,
    "registered": 240,
    "fee": "Free",
    "badge": "Trending",
    "status": "Open",
    "description": "Explore LLM architecture, prompt engineering, fine-tuning techniques, and multi-modal generative agents in real-world applications.",
    "speaker": "Dr. Ananya Ray"
  },
  {
    "id": 5,
    "title": "Robotics Arena: Line Follower & Combat Bot",
    "category": "Technical",
    "date": "2026-11-02",
    "time": "09:30 AM - 04:30 PM",
    "venue": "Campus Amphitheatre",
    "organizer": "Robotics & Automation Society",
    "capacity": 100,
    "registered": 100,
    "fee": "₹200",
    "badge": "Housefull",
    "status": "Closed",
    "description": "High-octane robotics showdown featuring autonomous line-following drones, micro-controllers, and custom combat bots.",
    "speaker": "Er. Mohit Trivedi"
  },
  {
    "id": 6,
    "title": "Annual Inter-College Cultural Gala: Spandan",
    "category": "Cultural",
    "date": "2026-11-08",
    "time": "05:00 PM - 10:00 PM",
    "venue": "University Open Grounds",
    "organizer": "Student Cultural Council",
    "capacity": 1000,
    "registered": 720,
    "fee": "Free",
    "badge": "Grand Event",
    "status": "Open",
    "description": "A vibrant celebration of music, street dance, theatrical plays, acoustic battles, and cultural traditions from across India.",
    "speaker": "Celebrity Guest Band"
  },
  {
    "id": 7,
    "title": "Cloud Native Architecture with Google Cloud",
    "category": "Workshop",
    "date": "2026-11-12",
    "time": "10:00 AM - 03:00 PM",
    "venue": "Computer Lab 401",
    "organizer": "Google Developer Student Clubs (GDSC)",
    "capacity": 90,
    "registered": 62,
    "fee": "Free",
    "badge": "Certified",
    "status": "Open",
    "description": "Containerization with Docker, Kubernetes pod orchestration, Cloud Run deployment, and microservices architecture.",
    "speaker": "Aman Verma (GCP Cloud Architect)"
  },
  {
    "id": 8,
    "title": "Campus Placement Masterclass: Cracking FAANG Interviews",
    "category": "Seminar",
    "date": "2026-11-15",
    "time": "03:00 PM - 06:00 PM",
    "venue": "Conference Hall 1",
    "organizer": "Training & Placement Cell",
    "capacity": 250,
    "registered": 230,
    "fee": "Free",
    "badge": "Crucial",
    "status": "Filling Fast",
    "description": "Resume crafting, DSA problem-solving roadmaps, system design fundamentals, and mock HR behavioral round strategies.",
    "speaker": "Alumni Mentors Panel"
  },
  {
    "id": 9,
    "title": "Inter-Department Cricket Championship",
    "category": "Sports",
    "date": "2026-11-20",
    "time": "08:00 AM - 05:00 PM",
    "venue": "Sports Pavilion Ground",
    "organizer": "Department of Physical Education",
    "capacity": 160,
    "registered": 140,
    "fee": "Free",
    "badge": "Sports",
    "status": "Open",
    "description": "Annual leather-ball 20-20 inter-department tournament competing for the prestigious Chancellor's Trophy.",
    "speaker": "Coach D. S. Randhawa"
  },
  {
    "id": 10,
    "title": "UI/UX Design Thinking & Figma Sprint",
    "category": "Workshop",
    "date": "2026-11-24",
    "time": "10:30 AM - 02:30 PM",
    "venue": "Design Studio 201",
    "organizer": "Creative Arts & Design Club",
    "capacity": 60,
    "registered": 48,
    "fee": "₹50",
    "badge": "Creative",
    "status": "Open",
    "description": "Master wireframing, interactive prototyping, micro-interactions, WCAG accessibility rules, and modern design systems.",
    "speaker": "Sneha Merchant (Lead Product Designer)"
  },
  {
    "id": 11,
    "title": "Speed Coding & Algorithmic Duel",
    "category": "Technical",
    "date": "2026-11-28",
    "time": "04:00 PM - 07:00 PM",
    "venue": "Lab 105 & CodeChef Platform",
    "organizer": "ACM Student Chapter",
    "capacity": 120,
    "registered": 110,
    "fee": "Free",
    "badge": "Contest",
    "status": "Filling Fast",
    "description": "Fast-paced algorithmic challenge testing dynamic programming, graph traversal, and time complexity optimization.",
    "speaker": "ACM ICPC Regionalists"
  },
  {
    "id": 12,
    "title": "Smart IoT & Embedded Systems Expo",
    "category": "Technical",
    "date": "2026-12-02",
    "time": "10:00 AM - 04:00 PM",
    "venue": "Electronics Lab & Corridor",
    "organizer": "ECE Department",
    "capacity": 120,
    "registered": 85,
    "fee": "Free",
    "badge": "Hardware",
    "status": "Open",
    "description": "Live demonstration of student-built IoT hardware projects including home automation, water monitoring, and AgriTech sensors.",
    "speaker": "Dr. K. N. Rao"
  },
  {
    "id": 13,
    "title": "Women in Tech Leadership Summit",
    "category": "Seminar",
    "date": "2026-12-06",
    "time": "11:00 AM - 01:30 PM",
    "venue": "Main Auditorium",
    "organizer": "Women in Engineering (WIE)",
    "capacity": 200,
    "registered": 160,
    "fee": "Free",
    "badge": "Empowerment",
    "status": "Open",
    "description": "Inspiring keynotes and career guidance panel with prominent female engineering directors, researchers, and founders.",
    "speaker": "Pooja Hegde & Dr. Vandana Shah"
  },
  {
    "id": 14,
    "title": "Open Source Contribution Day: Git & GitHub",
    "category": "Workshop",
    "date": "2026-12-10",
    "time": "01:30 PM - 05:00 PM",
    "venue": "Computer Lab 202",
    "organizer": "Open Source Community",
    "capacity": 70,
    "registered": 70,
    "fee": "Free",
    "badge": "Full",
    "status": "Closed",
    "description": "Hands-on guide to resolving merge conflicts, crafting pull requests, open-source licensing, and contributing to top repos.",
    "speaker": "Kunal Kushwaha Guest Mentor"
  },
  {
    "id": 15,
    "title": "Annual Badminton & Table Tennis Tournament",
    "category": "Sports",
    "date": "2026-12-14",
    "time": "09:00 AM - 06:00 PM",
    "venue": "Indoor Sports Complex",
    "organizer": "Sports Council",
    "capacity": 80,
    "registered": 68,
    "fee": "Free",
    "badge": "Sports",
    "status": "Open",
    "description": "Singles and doubles knockout tournament for boys and girls across all branches and semesters.",
    "speaker": "Sports Convener"
  },
  {
    "id": 16,
    "title": "Tech Talk: Blockchain, Smart Contracts & Solidity",
    "category": "Seminar",
    "date": "2026-12-18",
    "time": "02:00 PM - 04:30 PM",
    "venue": "Seminar Hall A",
    "organizer": "Web3 Student Guild",
    "capacity": 140,
    "registered": 95,
    "fee": "Free",
    "badge": "Web3",
    "status": "Open",
    "description": "Understanding distributed ledgers, Ethereum Virtual Machine (EVM), gas optimization, and deploying secure smart contracts.",
    "speaker": "Arjun Patel (Blockchain Developer)"
  }
],
        students: [
  {
    "id": 1,
    "name": "Dhanushi Patel",
    "enrollment": "25CS024",
    "department": "Computer Science",
    "semester": "3rd Sem",
    "cgpa": 9.45,
    "email": "dhanushi.patel@studenthub.edu",
    "phone": "+91 98250 12345",
    "country": "India",
    "state": "Gujarat",
    "city": "Ahmedabad",
    "status": "Dean's List",
    "skills": [
      "JavaScript",
      "Python",
      "Data Structures",
      "Web Dev"
    ]
  },
  {
    "id": 2,
    "name": "Aarav Sharma",
    "enrollment": "25CS001",
    "department": "Computer Science",
    "semester": "3rd Sem",
    "cgpa": 8.92,
    "email": "aarav.sharma@studenthub.edu",
    "phone": "+91 98251 23456",
    "country": "India",
    "state": "Gujarat",
    "city": "Surat",
    "status": "Active",
    "skills": [
      "C++",
      "Java",
      "DBMS",
      "Algorithms"
    ]
  },
  {
    "id": 3,
    "name": "Priya Verma",
    "enrollment": "25IT012",
    "department": "Information Technology",
    "semester": "3rd Sem",
    "cgpa": 9.15,
    "email": "priya.verma@studenthub.edu",
    "phone": "+91 98252 34567",
    "country": "India",
    "state": "Maharashtra",
    "city": "Mumbai",
    "status": "Dean's List",
    "skills": [
      "Full Stack",
      "Node.js",
      "MongoDB",
      "UI/UX"
    ]
  },
  {
    "id": 4,
    "name": "Rohan Mehta",
    "enrollment": "25CS045",
    "department": "Computer Science",
    "semester": "4th Sem",
    "cgpa": 7.85,
    "email": "rohan.mehta@studenthub.edu",
    "phone": "+91 98253 45678",
    "country": "India",
    "state": "Gujarat",
    "city": "Vadodara",
    "status": "Active",
    "skills": [
      "Python",
      "Flask",
      "SQL",
      "Networking"
    ]
  },
  {
    "id": 5,
    "name": "Ananya Iyer",
    "enrollment": "25AI008",
    "department": "AI & Data Science",
    "semester": "3rd Sem",
    "cgpa": 9.6,
    "email": "ananya.iyer@studenthub.edu",
    "phone": "+91 98254 56789",
    "country": "India",
    "state": "Karnataka",
    "city": "Bengaluru",
    "status": "Dean's List",
    "skills": [
      "Machine Learning",
      "PyTorch",
      "TensorFlow",
      "Statistics"
    ]
  },
  {
    "id": 6,
    "name": "Kavya Nair",
    "enrollment": "25EC034",
    "department": "Electronics & Comm.",
    "semester": "5th Sem",
    "cgpa": 8.7,
    "email": "kavya.nair@studenthub.edu",
    "phone": "+91 98255 67890",
    "country": "India",
    "state": "Karnataka",
    "city": "Bengaluru",
    "status": "Active",
    "skills": [
      "VLSI",
      "Embedded C",
      "IoT",
      "Arduino"
    ]
  },
  {
    "id": 7,
    "name": "Aditya Joshi",
    "enrollment": "25CS019",
    "department": "Computer Science",
    "semester": "3rd Sem",
    "cgpa": 8.4,
    "email": "aditya.joshi@studenthub.edu",
    "phone": "+91 98256 78901",
    "country": "India",
    "state": "Maharashtra",
    "city": "Pune",
    "status": "Active",
    "skills": [
      "React",
      "TypeScript",
      "Tailwind CSS",
      "Git"
    ]
  },
  {
    "id": 8,
    "name": "Ishaan Gupta",
    "enrollment": "25AI014",
    "department": "AI & Data Science",
    "semester": "4th Sem",
    "cgpa": 9.25,
    "email": "ishaan.gupta@studenthub.edu",
    "phone": "+91 98257 89012",
    "country": "India",
    "state": "Delhi",
    "city": "New Delhi",
    "status": "Dean's List",
    "skills": [
      "Deep Learning",
      "Computer Vision",
      "OpenCV",
      "Python"
    ]
  },
  {
    "id": 9,
    "name": "Sneha Kulkarni",
    "enrollment": "25IT028",
    "department": "Information Technology",
    "semester": "4th Sem",
    "cgpa": 8.35,
    "email": "sneha.kulkarni@studenthub.edu",
    "phone": "+91 98258 90123",
    "country": "India",
    "state": "Maharashtra",
    "city": "Pune",
    "status": "Active",
    "skills": [
      "Cyber Security",
      "Linux",
      "Bash",
      "Penetration Testing"
    ]
  },
  {
    "id": 10,
    "name": "Vikram Rathore",
    "enrollment": "25CS055",
    "department": "Computer Science",
    "semester": "5th Sem",
    "cgpa": 7.6,
    "email": "vikram.rathore@studenthub.edu",
    "phone": "+91 98259 01234",
    "country": "India",
    "state": "Gujarat",
    "city": "Rajkot",
    "status": "Active",
    "skills": [
      "Java",
      "Spring Boot",
      "MySQL",
      "Docker"
    ]
  },
  {
    "id": 11,
    "name": "Neha Choudhary",
    "enrollment": "25AI021",
    "department": "AI & Data Science",
    "semester": "3rd Sem",
    "cgpa": 8.88,
    "email": "neha.choudhary@studenthub.edu",
    "phone": "+91 98260 12345",
    "country": "India",
    "state": "Delhi",
    "city": "New Delhi",
    "status": "Active",
    "skills": [
      "NLP",
      "Hugging Face",
      "Pandas",
      "Scikit-Learn"
    ]
  },
  {
    "id": 12,
    "name": "Tanmay Sen",
    "enrollment": "25IT005",
    "department": "Information Technology",
    "semester": "6th Sem",
    "cgpa": 8.15,
    "email": "tanmay.sen@studenthub.edu",
    "phone": "+91 98261 23456",
    "country": "India",
    "state": "Gujarat",
    "city": "Ahmedabad",
    "status": "Graduating",
    "skills": [
      "AWS",
      "DevOps",
      "CI/CD",
      "Kubernetes"
    ]
  },
  {
    "id": 13,
    "name": "Alexander Miller",
    "enrollment": "25EX001",
    "department": "Computer Science",
    "semester": "3rd Sem",
    "cgpa": 9.1,
    "email": "alex.miller@studenthub.edu",
    "phone": "+1 408 555 0192",
    "country": "USA",
    "state": "California",
    "city": "San Jose",
    "status": "Active",
    "skills": [
      "Rust",
      "Distributed Systems",
      "Go",
      "Docker"
    ]
  },
  {
    "id": 14,
    "name": "Sophia Weber",
    "enrollment": "25EX002",
    "department": "AI & Data Science",
    "semester": "4th Sem",
    "cgpa": 9.3,
    "email": "sophia.weber@studenthub.edu",
    "phone": "+49 89 2180 0",
    "country": "Germany",
    "state": "Bavaria",
    "city": "Munich",
    "status": "Dean's List",
    "skills": [
      "Data Science",
      "R",
      "Python",
      "Bioinformatics"
    ]
  },
  {
    "id": 15,
    "name": "Liam Tremblay",
    "enrollment": "25EX003",
    "department": "Information Technology",
    "semester": "5th Sem",
    "cgpa": 8.65,
    "email": "liam.tremblay@studenthub.edu",
    "phone": "+1 416 978 2011",
    "country": "Canada",
    "state": "Ontario",
    "city": "Toronto",
    "status": "Active",
    "skills": [
      "Cloud Architecture",
      "GCP",
      "Terraform",
      "Python"
    ]
  },
  {
    "id": 16,
    "name": "Siddharth Rao",
    "enrollment": "25EC018",
    "department": "Electronics & Comm.",
    "semester": "3rd Sem",
    "cgpa": 7.95,
    "email": "siddharth.rao@studenthub.edu",
    "phone": "+91 98262 34567",
    "country": "India",
    "state": "Gujarat",
    "city": "Surat",
    "status": "Active",
    "skills": [
      "Robotics",
      "Microcontrollers",
      "MATLAB",
      "Signal Processing"
    ]
  }
],
        faqs: [
  {
    "id": 1,
    "question": "What is the minimum attendance criteria required to appear in end-semester exams?",
    "answer": "As per university academic regulations, students must maintain a minimum of 75% attendance in each registered theory subject and 80% in laboratory sessions to be eligible for final examinations.",
    "category": "Academic",
    "tags": [
      "attendance",
      "eligibility",
      "exam"
    ],
    "helpfulCount": 142,
    "lastUpdated": "2026-08-15"
  },
  {
    "id": 2,
    "question": "How do I calculate my Semester Grade Point Average (SGPA) and CGPA?",
    "answer": "SGPA is calculated as the sum of (Credit of Course × Grade Point Earned) divided by the total credits registered in that semester. CGPA is the cumulative weighted average of all semesters completed.",
    "category": "Academic",
    "tags": [
      "sgpa",
      "cgpa",
      "grades",
      "evaluation"
    ],
    "helpfulCount": 128,
    "lastUpdated": "2026-08-20"
  },
  {
    "id": 3,
    "question": "Where can I download the semester exam timetable and hall ticket?",
    "answer": "Examination timetables and hall tickets are published 2 weeks prior to exams on the StudentPortal under the 'Notices' and 'Examinations' tab. You must download and print the verified hall ticket.",
    "category": "Examinations",
    "tags": [
      "exam",
      "hall ticket",
      "timetable",
      "schedule"
    ],
    "helpfulCount": 195,
    "lastUpdated": "2026-09-01"
  },
  {
    "id": 4,
    "question": "What is the policy for re-evaluation and verification of answer scripts?",
    "answer": "Students can apply for answer script verification or re-evaluation within 10 days of grade publication through the online portal upon payment of the nominal verification fee.",
    "category": "Examinations",
    "tags": [
      "re-evaluation",
      "verification",
      "re-checking",
      "marks"
    ],
    "helpfulCount": 89,
    "lastUpdated": "2026-09-05"
  },
  {
    "id": 5,
    "question": "How can I connect to the Campus High-Speed Wi-Fi network?",
    "answer": "Connect to the SSID 'StudentHub-Campus-5G', open your browser, enter your student enrollment number and university portal password in the captive portal login screen.",
    "category": "Technical Support",
    "tags": [
      "wifi",
      "internet",
      "network",
      "credentials"
    ],
    "helpfulCount": 210,
    "lastUpdated": "2026-08-10"
  },
  {
    "id": 6,
    "question": "How do I reset my Student Portal or Institutional Email password?",
    "answer": "Click 'Forgot Password' on the login screen, enter your registered email/phone number, and follow the OTP verification link sent to your registered contact.",
    "category": "Technical Support",
    "tags": [
      "login",
      "password reset",
      "otp",
      "account"
    ],
    "helpfulCount": 174,
    "lastUpdated": "2026-08-25"
  },
  {
    "id": 7,
    "question": "How many books can a student borrow simultaneously from the central library?",
    "answer": "Undergraduate students can borrow up to 4 books for a 14-day duration, while postgraduate and honors students may borrow up to 6 books. Renewals can be done online through the OPAC portal.",
    "category": "Campus Facilities",
    "tags": [
      "library",
      "books",
      "borrowing",
      "opac"
    ],
    "helpfulCount": 97,
    "lastUpdated": "2026-08-18"
  },
  {
    "id": 8,
    "question": "What are the timings and access rules for computer laboratories?",
    "answer": "Computer labs are open from 8:30 AM to 7:00 PM on weekdays and 9:00 AM to 2:00 PM on Saturdays. Access requires wearing your institutional ID badge and adhering to lab safety protocols.",
    "category": "Campus Facilities",
    "tags": [
      "lab",
      "timings",
      "computers",
      "access"
    ],
    "helpfulCount": 83,
    "lastUpdated": "2026-08-22"
  },
  {
    "id": 9,
    "question": "What is the eligibility criterion for campus placement drives?",
    "answer": "Students typically require a minimum CGPA of 6.5 or above (varies by company), with no active backlogs at the time of recruitment, and a verified profile in the Training & Placement portal.",
    "category": "Placements",
    "tags": [
      "placement",
      "eligibility",
      "jobs",
      "cgpa",
      "backlog"
    ],
    "helpfulCount": 230,
    "lastUpdated": "2026-09-10"
  },
  {
    "id": 10,
    "question": "Are pre-final year students eligible for summer internship drives?",
    "answer": "Yes! Students entering their 6th/7th semester are encouraged to participate in official summer internship recruitment drives hosted between January and April each academic year.",
    "category": "Placements",
    "tags": [
      "internship",
      "summer training",
      "companies",
      "stipend"
    ],
    "helpfulCount": 165,
    "lastUpdated": "2026-09-12"
  },
  {
    "id": 11,
    "question": "How do I submit late medical certificates for condonation of attendance?",
    "answer": "Submit the original medical fitness certificate signed by a registered medical practitioner along with the parent's letter to the Department Academic Office within 7 working days of recovery.",
    "category": "Academic",
    "tags": [
      "medical leave",
      "condonation",
      "attendance",
      "doctor certificate"
    ],
    "helpfulCount": 112,
    "lastUpdated": "2026-08-30"
  },
  {
    "id": 12,
    "question": "How do I register for Open Elective and Minor Degree courses?",
    "answer": "Online course registration opens at the beginning of each semester. Log into StudentPortal > Academic Electives to choose preferred minor tracks (e.g. AI/ML, Cyber Security, FinTech).",
    "category": "Academic",
    "tags": [
      "elective",
      "minor degree",
      "course registration",
      "credits"
    ],
    "helpfulCount": 140,
    "lastUpdated": "2026-09-02"
  },
  {
    "id": 13,
    "question": "What are the rules regarding campus hostel accommodation and curfew?",
    "answer": "Hostel gates close at 9:30 PM. Late entry requires prior written approval from the Chief Warden. Bio-metric attendance is recorded daily at 9:45 PM.",
    "category": "Campus Facilities",
    "tags": [
      "hostel",
      "accommodation",
      "curfew",
      "warden"
    ],
    "helpfulCount": 78,
    "lastUpdated": "2026-08-12"
  },
  {
    "id": 14,
    "question": "How do I pay semester tuition or hostel fees online?",
    "answer": "Navigate to StudentPortal > Fee Payment. We support Net Banking, UPI (Google Pay / PhonePe / Paytm), and Credit/Debit cards with instant GST fee receipts generated.",
    "category": "Technical Support",
    "tags": [
      "fee payment",
      "receipt",
      "online payment",
      "upi"
    ],
    "helpfulCount": 204,
    "lastUpdated": "2026-09-14"
  },
  {
    "id": 15,
    "question": "What mock interview and aptitude preparation resources does the college provide?",
    "answer": "The T&P Cell organizes weekly coding contests on LeetCode/HackerRank, mock technical interviews with alumni, and provides free subscriptions to aptitude preparation portals.",
    "category": "Placements",
    "tags": [
      "mock interview",
      "aptitude",
      "dsa",
      "preparation"
    ],
    "helpfulCount": 188,
    "lastUpdated": "2026-09-16"
  },
  {
    "id": 16,
    "question": "Can I obtain a Bonafide Certificate or Transcripts through the portal?",
    "answer": "Yes, navigate to the Requests section in the portal, select 'Bonafide Certificate' or 'Transcript Request', specify the purpose, and download the digitally signed e-certificate within 48 hours.",
    "category": "Academic",
    "tags": [
      "bonafide",
      "transcripts",
      "certificate",
      "e-signed"
    ],
    "helpfulCount": 156,
    "lastUpdated": "2026-09-18"
  }
],
        locations: {
  "India": {
    "Gujarat": [
      "Ahmedabad",
      "Surat",
      "Vadodara",
      "Rajkot",
      "Gandhinagar",
      "Bhavnagar"
    ],
    "Maharashtra": [
      "Mumbai",
      "Pune",
      "Nagpur",
      "Nashik",
      "Aurangabad"
    ],
    "Karnataka": [
      "Bengaluru",
      "Mysuru",
      "Mangaluru",
      "Hubballi"
    ],
    "Delhi": [
      "New Delhi",
      "North Delhi",
      "South Delhi",
      "Dwarka"
    ],
    "Tamil Nadu": [
      "Chennai",
      "Coimbatore",
      "Madurai",
      "Tiruchirappalli"
    ]
  },
  "USA": {
    "California": [
      "San Jose",
      "San Francisco",
      "Los Angeles",
      "San Diego"
    ],
    "Texas": [
      "Austin",
      "Dallas",
      "Houston",
      "San Antonio"
    ],
    "New York": [
      "New York City",
      "Buffalo",
      "Rochester",
      "Albany"
    ]
  },
  "Germany": {
    "Bavaria": [
      "Munich",
      "Nuremberg",
      "Augsburg",
      "Regensburg"
    ],
    "Berlin": [
      "Berlin Mitte",
      "Spandau",
      "Pankow",
      "Charlottenburg"
    ],
    "Hesse": [
      "Frankfurt",
      "Wiesbaden",
      "Kassel",
      "Darmstadt"
    ]
  },
  "Canada": {
    "Ontario": [
      "Toronto",
      "Ottawa",
      "Mississauga",
      "Hamilton"
    ],
    "British Columbia": [
      "Vancouver",
      "Victoria",
      "Surrey",
      "Burnaby"
    ],
    "Quebec": [
      "Montreal",
      "Quebec City",
      "Laval",
      "Gatineau"
    ]
  }
}
    };
})(typeof window !== 'undefined' ? window : this);
