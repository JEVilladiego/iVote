-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 06, 2026 at 02:32 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ivote_cs`
--

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `name` varchar(160) NOT NULL,
  `course` varchar(80) NOT NULL,
  `partylist` varchar(100) DEFAULT '',
  `motto` text DEFAULT NULL,
  `platforms` text DEFAULT NULL,
  `achievements` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `election_id`, `position_id`, `student_id`, `name`, `course`, `partylist`, `motto`, `platforms`, `achievements`, `photo`, `created_at`) VALUES
(45, 3, 29, 'M2023-08472', 'Alexandra Marie Santos', 'BS Political Science', '', '\"Leading with heart, serving with purpose.\"', 'Establish a Student Welfare Fund for emergency financial assistance\r\nCreate a transparent online dashboard for council expenditures\r\nLaunch a mental health awareness campaign across all departments\r\nIntroduce free academic mentoring sessions every semester\r\nPartner with local businesses for student internship programs', 'Class President, College of Arts & Sciences – 3 consecutive years\r\nBest Delegate, Regional Model United Nations 2024\r\nDean\'s Lister for 5 consecutive semesters\r\nFounded the campus-wide Peer Tutoring Network with 120+ volunteers\r\nRecipient of the Outstanding Student Leader Award 2024', 'assets/img/candidates/PresAlexandra.PNG', '2026-04-04 17:30:55'),
(46, 3, 29, 'M2024-01935', 'Daniel Joseph Ramirez', 'BS Public Administration', '', '\"Unity in action, progress in vision.\"', 'Revamp the student grievance system with a 48-hour response guarantee\r\nBuild a campus sustainability program to reduce single-use plastics\r\nExpand scholarship linkages with five new corporate partners\r\nOrganize inter-departmental sports and cultural festivals quarterly\r\nCreate a safe space program for LGBTQ+ and minority students', 'National Youth Leadership Summit Delegate 2023\r\nOrganized the largest blood donation drive in school history (400+ donors)\r\nAcademic Excellence Awardee, 2022 & 2023\r\nCo-founder of the GreenCampus student-led environmental group\r\nRecognized by the city government for Outstanding Youth Volunteerism', 'assets/img/candidates/PresDaniel.PNG', '2026-04-04 17:30:55'),
(47, 3, 29, 'M2023-56218', 'Marcus Adrian Flores', 'BS Communication', '', '\"Every voice matters, every student counts.\"', 'Implement a student feedback system to evaluate council projects\r\nAdvocate for extended library and laboratory hours\r\nDevelop a campus safety protocol with a 24/7 student hotline\r\nPush for a free printing allowance for thesis and capstone students\r\nEstablish student chapters of professional organizations in every department', 'Journalism Award – Best Editorial, 2023 Regional Campus Press Conference\r\nStudent Council Secretary for two terms\r\nCompleted two international leadership exchange programs (Japan & South Korea)\r\nInitiated the \"Study Smarter\" workshop series attended by 500+ students\r\nCommunity Service Excellence Awardee, University Foundation Day 2024', 'assets/img/candidates/PresMarcus.PNG', '2026-04-04 17:30:55'),
(48, 3, 29, 'M2024-77304', 'Isabella Nicole Cruz', 'BS Computer Science', '', '\"Empowering students to shape their own future.\"', 'Launch a digital student ID and services portal for seamless transactions\r\nCreate an anti-discrimination policy drafted with student input\r\nIncrease transparency in council elections through live vote counting\r\nEstablish a campus career center with weekly industry talks\r\nPropose a student discount program with local transport and dining partners', 'National Essay Writing Champion, 2023 Philippine Youth Congress\r\nVice President for Academics, College Student Government 2023–2024\r\nGraduated with Highest Honors from Senior High School (Top 1 of batch)\r\nLaunched the \"Scholars Connect\" peer support program for working students\r\nFeatured in Campus Magazine\'s \"20 Under 20 Student Leaders\" list', 'assets/img/candidates/PresIsabella.PNG', '2026-04-04 17:30:55'),
(49, 3, 30, 'M2023-24091', 'Nathaniel Cruz', 'BS Public Administration', '', '\"Bridging gaps, building unity from within.\"', 'Create an internal communications hub for inter-department coordination\r\nConduct quarterly town hall meetings open to all students\r\nStandardize onboarding processes for new council officers\r\nEstablish a council-wide code of conduct and accountability system\r\nDevelop a student satisfaction survey issued every semester', 'Council Internal Affairs Coordinator for two terms\r\nOrganized 12 inter-department events with over 2,000 combined attendees\r\nBest Internal Coordinator, Regional Student Alliance 2023\r\nDean\'s Lister for 4 semesters\r\nLed the student consultation initiative that revised 3 major campus policies', 'assets/img/candidates/VpInNathaniel.PNG', '2026-04-04 17:30:55'),
(50, 3, 30, 'M2024-65827', 'Maria Angela Torres', 'BS Psychology', '', '\"From within us, we build something greater.\"', 'Launch a council mentoring program pairing seniors with freshmen\r\nDigitize internal council meeting minutes and make them publicly accessible\r\nCreate a wellness check-in program for council officers\r\nHost inter-college cultural exchange nights every term\r\nEstablish a clear escalation process for unresolved student concerns', 'Founding President, Campus Women Leaders Organization\r\nTop 3 Finalist, National Student Governance Excellence Awards 2024\r\nOrganized the school\'s first Mental Health Week with 800+ participants\r\nConsistent honor student for all six semesters\r\nFeatured speaker at the Regional Youth Conference on Student Governance 2023', 'assets/img/candidates/VpInMaria.PNG', '2026-04-04 17:30:55'),
(51, 3, 30, 'M2023-90136', 'Kevin Paolo Mendoza', 'BS Political Science', '', '\"Stronger together — one council, one campus.\"', 'Build a digital grievance tracking system for student complaints\r\nHost leadership workshops every semester for junior officers\r\nEnsure fair representation of all programs in council activities\r\nCreate a peer conflict resolution panel for student disputes\r\nAdvocate for inclusive events that celebrate campus diversity', 'Council President Pro-Tem during the 2023–2024 term\r\nReceived the Most Outstanding Council Member award\r\nPlanned and executed the largest Inter-College Festival in school history\r\nCompleted a leadership immersion program at the Ateneo Leadership Institute\r\nActive advocate in the university\'s anti-bullying campaign since 2022', 'assets/img/candidates/VpInKevin.PNG', '2026-04-04 17:30:55'),
(52, 3, 30, 'M2024-11289', 'Jasmine Nicole Bautista', 'BS Psychology', '', '\"Listen more, act better, lead with empathy.\"', 'Create an anonymous student feedback platform for council improvement\r\nIntroduce a \"Student of the Month\" recognition program\r\nOrganize semestral retreats for council officers to foster teamwork\r\nPush for sign-language interpretation at all major campus events\r\nCoordinate with deans to reduce bureaucratic barriers for student requests', 'Pioneered the inclusive events framework adopted by 3 student organizations\r\nStudent Regent Representative nominee for Academic Year 2023–2024\r\nGraduated Magna Cum Laude from Senior High School\r\nFounded the campus sign-language club with 60+ active members\r\nNational Finalist, Youth for Inclusive Governance Summit 2024', 'assets/img/candidates/VpInJasmine.PNG', '2026-04-04 17:30:55'),
(53, 3, 31, 'M2023-43765', 'Carlo Miguel Navarro', 'BS Political Science', '', '\"Our campus, connected to the world.\"', 'Forge MOUs with five universities for student exchange programs\r\nRepresent student concerns in national youth policy forums\r\nEstablish alumni mentoring linkages for career guidance\r\nCoordinate joint events with neighboring universities annually\r\nBuild a community extension arm for regular outreach activities', 'Delegate, National Youth Parliament 2023 & 2024\r\nLed a successful inter-university debate collaboration\r\nExternal Relations Officer, College Student Government 2023\r\nRecipient of the Youth Diplomat Award from the city government\r\nOrganized 4 community outreach missions with 300+ volunteer students', 'assets/img/candidates/VpExCarlo.PNG', '2026-04-04 17:30:55'),
(54, 3, 31, 'M2024-98502', 'Angela Patricia Lopez', 'BS Communication', '', '\"Building bridges beyond campus walls.\"', 'Create a student ambassador program for university representation at events\r\nDevelop a social media strategy to boost the council\'s public presence\r\nSecure corporate sponsorships to fund student-led projects\r\nInitiate a community service partnership with 3 barangays near campus\r\nLobby for student seat representation in city youth council', 'Best Delegate, National Intercollegiate Model UN 2023\r\nPress Relations Officer, College Student Government 2022–2023\r\nManaged the university\'s social media presence growing followers by 40%\r\nAwarded Most Outstanding Extern, Regional Student Leaders Summit\r\nLed the first ever cross-university charity gala raising ₱250,000 for scholars', 'assets/img/candidates/VpExAngela.PNG', '2026-04-04 17:30:55'),
(55, 3, 31, 'M2023-30674', 'Joshua Daniel Reyes', 'BS Public Administration', '', '\"Every partnership is a step forward for every student.\"', 'Negotiate student discounts with surrounding commercial establishments\r\nPublish a monthly external affairs report accessible to all students\r\nOrganize an annual regional student summit hosted at our campus\r\nEstablish a linkage with NGOs for volunteer opportunities abroad\r\nPush for formal inclusion in local government youth consultations', 'External Liaison, University Research and Publication Office 2023\r\nFirst Place, Inter-School Public Speaking Contest 2023\r\nCoordinated 3 MOUs between the college and private industry partners\r\nYouth Representative, Local Development Council 2024\r\nFeatured in the national newspaper for youth advocacy efforts', 'assets/img/candidates/VpExJoshua.PNG', '2026-04-04 17:30:55'),
(56, 3, 32, 'M2024-52411', 'Clarisse Mae Delgado', 'BS Business Administration', '', '\"Organized records, transparent governance.\"', 'Digitize all council documents and make them accessible via a student portal\r\nPublish official meeting minutes within 24 hours of each session\r\nStreamline the accreditation process for student organizations\r\nCreate a master calendar of all council and student org events\r\nSet up a secure cloud-based archive of all council correspondence', 'Secretary, College Student Government for two consecutive terms\r\nDeveloped the council\'s first-ever digital documentation system\r\nOutstanding Secretary Award, Regional Student Governance Forum 2023\r\nDean\'s Lister for 5 semesters\r\nCompleted a records and information management certification course', 'assets/img/candidates/GenSecClarisse.PNG', '2026-04-04 17:30:55'),
(57, 3, 32, 'M2023-77893', 'Rafael Antonio Gomez', 'BS Political Science', '', '\"Precision in documentation, clarity in action.\"', 'Introduce a student-facing council project tracker updated in real time\r\nEstablish a formalized resolution process for student legislative proposals\r\nEnsure all council decisions are communicated within 48 hours\r\nCreate a shared Google Workspace for all council departments\r\nConduct a semestral audit of council processes for continuous improvement', 'Legal Research Intern, University Office of the Ombudsman 2023\r\nTop 1, College of Public Administration Academic Excellence Awards\r\nDrafted 3 campus policy proposals adopted by the administration\r\nConsistent First Honor student since first year\r\nPublished a paper on student governance in a peer-reviewed journal', 'assets/img/candidates/GenSecRafael.PNG', '2026-04-04 17:30:55'),
(58, 3, 32, 'M2024-26350', 'Lianne Marie Castillo', 'BS Business Administration', '', '\"Every detail matters when every student is counting on us.\"', 'Introduce a student complaint and resolution log visible to all students\r\nCoordinate with departments to standardize form and document templates\r\nTrain incoming officers on proper documentation and record-keeping\r\nOrganize a filing and archive cleanup at the start of every academic year\r\nCreate a student FAQ resource based on most-asked council questions', 'Assistant Secretary, University Student Alliance 2023–2024\r\nBest Thesis Writer, College of Business Administration 2024\r\nOrganized the council\'s first-ever documentation bootcamp for officers\r\nHonor Roll student for all enrolled semesters\r\nReceived a scholarship from the President\'s Excellence Award', 'assets/img/candidates/GenSecLianne.PNG', '2026-04-04 17:30:55'),
(59, 3, 33, 'M2023-69027', 'Kevin Louie Tan', 'BS Education', '', '\"Support the secretary, empower the council.\"', 'Maintain real-time council attendance records and publish them monthly\r\nAssist in building an online student concerns inbox with response tracking\r\nCreate a backup protocol for all council digital files\r\nDraft templates for all recurring council communications\r\nSupport the general secretary in preparing legislative reports each term', 'Assistant Council Secretary for 2022–2023\r\nBest in Parliamentary Procedure, Regional Student Council Convention\r\nCompleted a digital records management workshop\r\nAcademic Excellence Awardee, College of Education\r\nLed the documentation team during the 2023 Student Leadership Summit', 'assets/img/candidates/DepSecKevin.PNG', '2026-04-04 17:30:55'),
(60, 3, 33, 'M2024-14588', 'Sophia Anne Mercado', 'BS Business Administration', '', '\"In every memo, in every minute — I serve.\"', 'Prepare comprehensive briefing materials before every council meeting\r\nOversee correspondences between the council and university administration\r\nImplement a shared task management board for council officers\r\nEnsure all resolutions passed are encoded, distributed, and followed up\r\nPilot a student-facing council update bulletin released bi-weekly', 'Vice President for Records, College Student Organization 2023\r\nOutstanding Student Secretary, University Recognition Night 2023\r\nMagna Cum Laude candidate with a current GPA of 1.28\r\nOrganized 3 inter-department orientation programs for incoming freshmen\r\nCompleted an administrative leadership training at the Local Government Academy', 'assets/img/candidates/DepSecSophia.PNG', '2026-04-04 17:30:55'),
(61, 3, 33, 'M2023-83146', 'Adrian Paul Soriano', 'BS Political Science', '', '\"Behind every great council is a great deputy.\"', 'Build a centralized council email system to track all outgoing communications\r\nAssist in preparing council annual reports for public release\r\nCreate an onboarding handbook for new and incoming council officers\r\nEnsure no student concern is left unrecorded or unanswered\r\nCoordinate with the auditor to cross-check council project reports', 'Documentation Head, College Week Organizing Committee 2023\r\nBest Research Paper, Political Science Department 2023\r\nConsistent Dean\'s Lister with 6 semesters of academic excellence\r\nCompleted a governance and accountability workshop at DLSU\r\nNamed \"Most Dependable Officer\" by the outgoing council', 'assets/img/candidates/DepSecAdrian.PNG', '2026-04-04 17:30:55'),
(62, 3, 34, 'M2022-00801', 'Maria L. Santos', 'BS Accountancy', '', '\"Every peso accountable, every centavo counted.\"', 'Publish monthly financial reports accessible to all students online\r\nIntroduce a digital receipting system for all council transactions\r\nPropose a student emergency fund with transparent disbursement criteria\r\nEstablish a finance committee with student representatives\r\nAudit all council expenditures each semester and release findings', 'Treasurer, College Student Government 2022–2024\r\nCompleted a public financial management course at UP Diliman\r\nReceived the Most Transparent Officer Award from the university auditor\r\nManaged a ₱500,000 council budget with zero audit findings in two years\r\nTop 5, National Accounting Students Competition 2023', 'assets/img/candidates/TreasurerMaria.PNG', '2026-04-04 17:30:55'),
(63, 3, 34, 'M2022-00802', 'Carlo R. Mendoza', 'BS Accountancy', '', '\"Financial integrity is the foundation of student trust.\"', 'Create a student-accessible budget planning portal each semester\r\nReduce administrative fees for student organization accreditation\r\nNegotiate lower venue and supplier costs for council events\r\nPropose a tiered dues system to ease the financial burden on scholars\r\nLaunch a fundraising program to supplement council operating funds', 'Finance Chair, University-Wide Student Alliance 2023\r\nCPA Board Exam Passer (Ranked 12th nationally) 2024\r\nProduced the council\'s first-ever audited financial statements\r\nAwarded Best Finance Officer, Regional Student Congress 2023\r\nLed a financial literacy seminar attended by 400+ students', 'assets/img/candidates/TreasurerCarlo.PNG', '2026-04-04 17:30:55'),
(64, 3, 34, 'M2022-00803', 'Angela P. Ramirez', 'BS Accountancy', '', '\"Smart spending, better futures for every student.\"', 'Introduce zero-based budgeting for all council departments\r\nCreate a student petition process for reallocation of council funds\r\nPartner with the finance department for pro-bono budget consultations\r\nSet up a council savings fund for large-scale annual projects\r\nEnsure all student fees are itemized and published before collection', 'External Auditor, College of Business Student Council 2022\r\nConsistent Dean\'s Lister with 1.0 GPA in all Finance subjects\r\nScholarship grantee under the Academic Excellence Program\r\nOrganized the first Inter-College Finance Fair with 15 organizations participating\r\nRecipient of the Foundation Day Academic Achievement Medal 2023', 'assets/img/candidates/TreasurerAngela.PNG', '2026-04-04 17:30:55'),
(65, 3, 35, 'M2022-00804', 'John M. Cruz', 'BS Accountancy', '', '\"Watchful, fair, and accountable — always.\"', 'Conduct mid-year and end-of-year audits of all council accounts\r\nCreate an independent audit committee composed of non-council students\r\nDigitize all receipts and financial documents for easy verification\r\nEnsure all financial reports are released 10 days after each event\r\nEstablish a whistleblower process for reporting financial irregularities', 'External Auditor, University Student Services Office 2023\r\nPassed the Mock CPA Board Examination with a score of 89%\r\nDeveloped the council\'s first standardized audit checklist\r\nTop 1, Accountancy Department – 3 consecutive semesters\r\nAwarded the Integrity in Governance Award by the school administration', 'assets/img/candidates/AuditorJohn.PNG', '2026-04-04 17:30:55'),
(66, 3, 35, 'M2022-00805', 'Patricia D. Flores', 'BS Accountancy', '', '\"Numbers don\'t lie — and neither will I.\"', 'Introduce a real-time council spending tracker viewable by all students\r\nRequire three-quote bidding for all council purchases above ₱5,000\r\nPublish a post-event financial summary within 5 days of each activity\r\nTrain all council officers in basic bookkeeping and documentation\r\nCreate an audit report template for uniformity and clarity', 'Internal Auditor, College of Accountancy Student Society 2023\r\nNational Finalist, Young Accountants Excellence Challenge 2024\r\nCo-authored a governance reform proposal submitted to the University Board\r\nRecipient of the Presidential Scholarship for Academic Merit\r\nCompleted an advanced financial auditing course from the Philippine Institute of CPAs', 'assets/img/candidates/AuditorPatricia.PNG', '2026-04-04 17:30:55'),
(67, 3, 35, 'M2022-00806', 'Mark A. Villanueva', 'BS Accountancy', '', '\"Transparency starts with the numbers.\"', 'Establish a public register of all council assets and their conditions\r\nRequire a pre-event and post-event budget comparison report for all activities\r\nCoordinate with the university COA for best practices in student audit\r\nPublish an annual state-of-finances report with charts and analysis\r\nPropose sanctions for officers who fail to submit required financial documents', 'Accounting Society President, 2023–2024\r\nCampus Journalist Best in Finance Reporting, 2023\r\nCompleted government auditing training at the Commission on Audit Academy\r\nDean\'s Lister for all 7 semesters enrolled\r\nLed the reconciliation of 3 years of unaudited council financial records', 'assets/img/candidates/AuditorMark.PNG', '2026-04-04 17:30:55'),
(68, 3, 36, 'M2022-00807', 'Daniel T. Reyes', 'BS Business Administration', '', '\"Resourceful, strategic, and always student-first.\"', 'Set up a council merchandise store with profits directed to student programs\r\nNegotiate supplier deals to cut event costs by at least 20%\r\nCreate a sponsorship package for businesses to support council activities\r\nEstablish a student enterprise booth program during campus events\r\nDevelop a council procurement manual to ensure fair and competitive bidding', 'Business Development Officer, College of Business Student Council 2023\r\nWon 1st Place, National Business Plan Competition 2023\r\nNegotiated ₱80,000 in sponsorships for the 2023 Foundation Day celebration\r\nCompleted a business management certificate from the Asian Institute of Management\r\nRecognized as Most Outstanding Business Officer by the regional student alliance', 'assets/img/candidates/BMDaniel.PNG', '2026-04-04 17:30:55'),
(69, 3, 36, 'M2022-00808', 'Kristine C. Bautista', 'BS Business Administration', '', '\"Smart resources, meaningful outcomes for every student.\"', 'Launch a student-run café as a self-sustaining council revenue source\r\nCreate a vendor accreditation process for campus events\r\nBuild a council property inventory system updated after every activity\r\nIntroduce quarterly financial planning sessions with all council officers\r\nSeek CSR partnerships with companies to fund scholarships and welfare programs', 'Marketing Director, University Entrepreneurship Week 2023\r\nNational Finalist, Young Entrepreneurs Summit 2024\r\nRaised ₱120,000 through student-led fundraising events in one academic year\r\nReceived the Outstanding Student Entrepreneur Award from DOST 2024\r\nCompleted a social enterprise management course at the Ateneo de Manila University', 'assets/img/candidates/BMKristine.PNG', '2026-04-04 17:30:55'),
(70, 3, 36, 'M2022-00809', 'Adrian S. Navarro', 'BS Business Administration', '', '\"Maximizing every resource for maximum student impact.\"', 'Introduce a council equipment rental program for student organizations\r\nDevelop an annual sponsorship drive targeting local and national companies\r\nCreate an emergency fund sourced from council-managed business activities\r\nPublish a quarterly business operations report for the student body\r\nPropose a student loyalty program tied to accredited campus vendors', 'Business Operations Head, 2023 University Leadership Summit\r\nBest Business Plan, Entrepreneurship Department Finals 2023\r\nFacilitated ₱200,000 in grants from private sector partners in 2023\r\nManaged logistics and procurement for 8 major council events\r\nCompleted a business analytics course from the University of the Philippines Open University', 'assets/img/candidates/BMAdrian.PNG', '2026-04-04 17:30:55'),
(71, 3, 37, 'M2022-00810', 'Camille R. Garcia', 'BS Communication', '', '\"Informed students are empowered students.\"', 'Rebrand and refresh all council social media platforms for better reach\r\nLaunch a bi-weekly council newsletter sent to every student email\r\nCreate a campus bulletin board (physical and digital) updated weekly\r\nIntroduce live streaming of all council public meetings and events\r\nDevelop a student feedback form published after every major council activity', 'Campus journalist of the year, University Student Press 2023\r\nSocial Media Manager, College Student Government 2022–2024\r\nGrew council Instagram following by 60% in one semester\r\nBest in Feature Writing, Regional Campus Press Conference 2023\r\nCompleted a digital communications and social media management certification', 'assets/img/candidates/PIOCamille.PNG', '2026-04-04 17:30:55'),
(72, 3, 37, 'M2022-00811', 'Joshua P. Castillo', 'BS Journalism', '', '\"Every student deserves to know what\'s happening.\"', 'Create a council mobile-friendly website with announcements and resources\r\nStart a \"Council Spotlight\" video series featuring student stories\r\nEstablish a press release protocol for all council events and decisions\r\nTrain council officers in basic media communication and public speaking\r\nBuild a crisis communication plan for sensitive student issues', 'Editor-in-Chief, University Official Student Publication 2023–2024\r\nGold Award Winner, National Campus Journalism Awards 2023\r\nProduced a documentary on student life that screened at 3 youth film festivals\r\nCompleted a journalism and media law workshop at the Philippine Press Institute\r\nRecognized as Top 5 Most Influential Campus Journalists in the region', 'assets/img/candidates/PIOJoshua.PNG', '2026-04-04 17:30:55'),
(73, 3, 37, 'M2022-00812', 'Nicole M. Aquino', 'BS Communication', '', '\"Truth, clarity, and connection — one post at a time.\"', 'Design and implement a unified visual identity for all council publications\r\nCreate an FAQ database addressing the most common student questions\r\nHost monthly \"Ask the Council\" open Q&A sessions online and on-campus\r\nIntroduce infographic summaries for complex student policies and guidelines\r\nPartner with campus radio and TV stations for wider council coverage', 'Communications Coordinator, University Foundation Day 2023\r\nBest Layout Artist, Regional Campus Press Conference 2022 & 2023\r\nDesigned the official visual assets for 15 major campus events\r\nCompleted a course on graphic design and visual storytelling at CIIT Philippines\r\nRecipient of the Creative Excellence Award from the College of Arts', 'assets/img/candidates/PIONicole.PNG', '2026-04-04 17:30:55'),
(74, 3, 38, 'M2023-10427', 'Angela Marie Santos', 'BS Biology', '', '\"Science and service, hand in hand.\"', 'Lobby for additional research microscopes and lab equipment for Biology students\r\nEstablish a bio-lab open access schedule for thesis and research students\r\nOrganize monthly science talks featuring professional biologists and researchers\r\nPush for a Biology student travel grant to attend national science conferences\r\nCoordinate a campus biodiversity walk to promote environmental awareness', 'Best Research Paper, Regional Biology Olympiad 2023\r\nLaboratory Assistant Awardee, Biology Department 2022–2023\r\nOrganized the campus\'s first Nature Photography Exhibition\r\nDean\'s Lister for 4 semesters\r\nMember of the Philippine Society of Microbiology (Student Chapter)', 'assets/img/candidates/BioAngela.PNG', '2026-04-04 17:30:55'),
(75, 3, 38, 'M2024-11836', 'Carlo Miguel Ramirez', 'BS Biology', '', '\"For the students who study life — I will fight for yours.\"', 'Push for extended laboratory hours for undergraduate research projects\r\nCreate a Biology student resource library with free access to scientific journals\r\nOrganize a mentoring program connecting undergraduates with grad students\r\nAdvocate for a Biology field trip budget restored to pre-pandemic levels\r\nEstablish an eco-garden maintained by Biology students on campus', 'Best Presenter, University Undergraduate Research Symposium 2023\r\nEco-Campus Volunteer of the Year, 2023\r\nPublished a co-authored research article in a regional science journal\r\nTop 10, National Biology Quiz Bee 2022\r\nFounding member of the campus Wildlife Conservation Club', 'assets/img/candidates/BioCarlo.PNG', '2026-04-04 17:30:55'),
(76, 3, 38, 'M2022-09215', 'Bianca Louise Navarro', 'BS Biology', '', '\"Every organism matters — and so does every Bio student.\"', 'Push for a dedicated thesis consultation room for Biology seniors\r\nOrganize a Biology alumni network for career mentoring and job referrals\r\nCreate a shared reagent and specimen repository to reduce student expenses\r\nAdvocate for a departmental lounge and quiet study space for Biology students\r\nPartner with the city government for community-based environmental projects', 'Biology Department Council Representative 2022–2023\r\nOutstanding Student Research Award, University Recognition Night 2023\r\nVolunteer scientist at the Philippine Biodiversity Monitoring Program\r\nCompleted an advanced ecology field course at the UP Visayas Marine Station\r\nSumma Cum Laude candidate with consistent 1.0 grades in Biology majors', 'assets/img/candidates/BioBianca.PNG', '2026-04-04 17:30:55'),
(77, 3, 39, 'M2023-10652', 'Daniel Joseph Cruz', 'BS Computer Science', '', '\"Code the change you want to see in your campus.\"', 'Push for an upgrade of the CS department\'s computers and software licenses\r\nEstablish a free coding bootcamp series for non-CS students\r\nCreate a CS student job board with verified internship and freelance listings\r\nOrganize a hackathon addressing real campus problems each semester\r\nLobby for a 24/7 CS laboratory access policy for thesis students', 'Champion, National Intercollegiate Programming Contest 2023\r\nDeveloped the council\'s first mobile voting app prototype\r\nGoogle Developer Student Club Lead, 2023–2024\r\nInternship at a top-5 Philippine tech company while maintaining a 1.5 GPA\r\nRecipient of the DOST-SEI Computing Scholarship', 'assets/img/candidates/CSDaniel.PNG', '2026-04-04 17:30:55'),
(78, 3, 39, 'M2024-12144', 'Patricia Anne Villanueva', 'BS Computer Science', '', '\"Tech should work for every student, not just the few.\"', 'Advocate for free open-source software tools for all CS students\r\nLaunch a Women in Tech campus chapter to support female CS students\r\nOrganize weekly peer programming and debugging sessions\r\nNegotiate with tech companies for student license discounts and free tools\r\nCreate a digital portfolio workshop to help CS students land internships', '1st Place, Regional Women in Tech Hackathon 2023\r\nMicrosoft Student Ambassador, 2023–2024\r\nGraduated Valedictorian from Senior High School – STEM track\r\nCompleted 3 Coursera certifications in AI, cloud computing, and cybersecurity\r\nFounded the campus cybersecurity awareness club with 80+ members', 'assets/img/candidates/CSPatricia.PNG', '2026-04-04 17:30:55'),
(79, 3, 39, 'M2025-13509', 'Kevin Matthew Torres', 'BS Computer Science', '', '\"From algorithms to advocacy — I\'ve got CS students covered.\"', 'Push for a CS mentoring app connecting students with industry professionals\r\nCreate a student-run open source project hub for collaborative development\r\nLobby for an updated CS curriculum aligned with current industry demands\r\nOrganize an annual career fair targeting IT and software companies\r\nEstablish a hardware lending library for CS project development', 'Best Capstone Project, College of Computer Studies 2024\r\nAWS Certified Cloud Practitioner at age 19\r\nCore Developer, award-winning campus attendance monitoring system\r\nTop 2, National Information Technology Skills Competition 2023\r\nCompleted an AI/ML immersion program at the Asian Development Bank Institute', 'assets/img/candidates/CSKevin.PNG', '2026-04-04 17:30:55'),
(80, 3, 40, 'M2022-08973', 'Maria Therese Bautista', 'BS Human Services', '', '\"Serving others is the highest form of leadership.\"', 'Establish a community practicum support group for HS students\r\nAdvocate for hazard pay equivalents for students doing fieldwork\r\nCreate a mental health support channel specifically for Human Services students\r\nOrganize inter-agency visits to broaden career awareness for HS students\r\nPush for a departmental scholarship for students with high community service hours', 'Best Practicum Student, DSWD Regional Office Collaboration 2023\r\nVolunteer Coordinator, Tondo Community Feeding Program\r\nOutstanding Community Extension Volunteer, University Recognition Night 2023\r\nDean\'s Lister for all enrolled semesters\r\nPublished a policy brief on student social work challenges in a local journal', 'assets/img/candidates/HSMaria.PNG', '2026-04-04 17:30:55'),
(81, 3, 40, 'M2023-11064', 'Joshua Daniel Flores', 'BS Human Services', '', '\"Human services is not just a degree — it\'s a mission.\"', 'Lobby for a dedicated transportation allowance for off-campus immersions\r\nCreate an HS alumni linkage for career mentoring and job placement\r\nOrganize a semester-long community development project in a partner barangay\r\nPush for a Human Services library corner stocked with social work references\r\nEstablish an emergency fund for HS students facing financial hardship', 'Regional Youth Volunteer of the Year, DSWD Region IV-A 2023\r\nCo-authored a community development manual used in 3 municipalities\r\nCompleted an international social development training in Thailand\r\nTop Graduate Nominee, College of Social Work 2024\r\nManaged a livelihood program benefiting 50 families in partnership with an LGU', 'assets/img/candidates/HSJoshua.PNG', '2026-04-04 17:30:55'),
(82, 3, 40, 'M2024-11908', 'Camille Andrea Mendoza', 'BS Human Services', '', '\"Compassion drives change — and I will lead that change.\"', 'Organize a social welfare summit connecting HS students with government agencies\r\nPush for a recognition system honoring students with exceptional community hours\r\nCreate a digital resource hub for HS students with research papers and case studies\r\nCoordinate with the DSWD for funded internship slots exclusively for HS students\r\nAdvocate for a revised practicum schedule that respects student academic load', 'Best Social Work Intern, Quezon City Social Welfare Department 2023\r\nEstablished a campus feeding program that served 200+ indigent students weekly\r\nMagna Cum Laude candidate with a GPA of 1.32\r\nCompleted a child protection specialist course from UNICEF Philippines\r\nNational Finalist, Student Social Work Excellence Awards 2024', 'assets/img/candidates/HSCamille.PNG', '2026-04-04 17:30:55'),
(83, 3, 41, 'M2023-10795', 'Nicole Samantha Reyes', 'BS Psychology', '', '\"Mental health is not a privilege — it\'s a right.\"', 'Push for a free counseling session quota for all Psychology students each semester\r\nEstablish a peer support network trained in psychological first aid\r\nOrganize awareness campaigns for mental health during exam seasons\r\nLobby for a Psychology research lab with updated psychometric tools\r\nCreate an advocacy group addressing depression and anxiety among students', 'Peer Counselor Coordinator, University Guidance and Counseling Center 2023\r\nBest Presenter, Regional Psychology Research Symposium 2023\r\nTrained in Mental Health First Aid by the Department of Health\r\nDean\'s Lister with 1.4 GPA in Psychology major subjects\r\nCompleted an online certification in Cognitive Behavioral Therapy Foundations', 'assets/img/candidates/PsyNicole.PNG', '2026-04-04 17:30:56'),
(84, 3, 41, 'M2024-12251', 'Adrian Paolo Castillo', 'BS Psychology', '', '\"Understanding minds, building a stronger campus community.\"', 'Introduce a bi-weekly drop-in \"Talk Space\" session facilitated by Psych seniors\r\nCoordinate with HR professionals to offer students career guidance in I/O Psychology\r\nPush for updated psychological assessment tools in the department\'s test library\r\nOrganize a mental health week with activities designed by Psychology students\r\nAdvocate for the integration of psychological wellness modules in all programs', 'Best in Psychological Research, College of Education and Psychology 2023\r\nCompleted an internship at a DOH-accredited mental health institution\r\nSpeaker, Regional Youth Mental Health Forum 2023\r\nTop 5, National Psychology Licensure Mock Board Examination 2024\r\nFounded the \"Mind Over Stigma\" campus movement with 300+ advocates', 'assets/img/candidates/PsyAdrian.PNG', '2026-04-04 17:30:56'),
(85, 3, 41, 'M2025-13466', 'Hannah Beatrice Dela Cruz', 'BS Psychology', '', '\"Healing communities starts with understanding people.\"', 'Create a student mental health tracker app developed by Psychology students\r\nEstablish a Psychology student-run clinic for free psychological testing\r\nLobby for at least one licensed psychologist on campus available daily\r\nOrganize inter-college seminars on emotional intelligence and stress resilience\r\nPush for a peer reviewer program for Psychology undergraduate theses', 'Campus Mental Health Advocate of the Year 2023, recognized by the university president\r\nInternational youth delegate, ASEAN Youth Mental Health Congress 2023\r\nMagna Cum Laude candidate with no grade below 1.5\r\nCo-designed a mental health toolkit now used by 5 campus organizations\r\nFeatured in a national magazine article on youth mental health leadership', 'assets/img/candidates/PsyHannah.PNG', '2026-04-04 17:30:56'),
(86, 3, 42, 'M2022-09384', 'Rafael Lorenzo Aquino', 'BS Mathematics', '', '\"Solving problems — in and out of the classroom.\"', 'Establish a Mathematics tutorial center open to all students at no charge\r\nPush for expanded access to MATLAB, Mathematica, and other software tools\r\nOrganize a Math Olympiad to promote excellence among Math students\r\nCreate a thesis support group for senior Math students nearing graduation\r\nLobby for a dedicated Math faculty-student consultation schedule every week', 'Gold Medalist, Regional Mathematics Olympiad 2023\r\nNational Qualifying Round Passer, Philippine Mathematical Olympiad 2022 & 2023\r\nDeveloped a free math tutoring module used by 3 public high schools nearby\r\nDean\'s Lister for 6 consecutive semesters\r\nCompleted an advanced calculus and number theory course at the UP Institute of Mathematics', 'assets/img/candidates/MathRafael.PNG', '2026-04-04 17:30:56'),
(87, 3, 42, 'M2023-11172', 'Christine Joy Mercado', 'BS Mathematics', '', '\"Mathematics is the language of change — and I speak it for you.\"', 'Organize monthly problem-solving workshops open to all year levels\r\nAdvocate for a Math student lounge and study hub with whiteboards and resources\r\nPush for the introduction of applied math electives relevant to industry needs\r\nCreate a Math peer-tutoring roster with compensation recognition for volunteer tutors\r\nPropose a data science and statistics elective for Math students', 'Silver Medal, National Intercollegiate Mathematics Competition 2023\r\nCompleted a data science certificate from Coursera and IBM\r\nFounded the campus Statistics and Data Science Interest Group\r\nAcademic Excellence Awardee, College of Science for two consecutive years\r\nFacilitated free review sessions helping 30+ students pass their math subjects', 'assets/img/candidates/MathChristine.PNG', '2026-04-04 17:30:56'),
(88, 3, 42, 'M2024-12039', 'Vincent Paul Herrera', 'BS Mathematics', '', '\"Equations have answers — and so do student problems.\"', 'Launch a Math career orientation program highlighting actuarial, finance, and data careers\r\nNegotiate access to premium academic journal databases for Math research students\r\nEstablish an annual Math symposium inviting industry professionals\r\nCreate a peer-graded problem set exchange program between Math classes\r\nPush for a relaxed retake policy for high-unit Math subjects', 'Top 1, Actuarial Science Pre-Board Examination 2024\r\nCompleted a financial mathematics certification from the Society of Actuaries\r\nBest Capstone Thesis, Department of Mathematics 2024\r\nConsistent President\'s Lister for all enrolled semesters\r\nSpeaker, National Youth in Mathematics Symposium 2023', 'assets/img/candidates/MathVincent.PNG', '2026-04-04 17:30:56');

-- --------------------------------------------------------

--
-- Table structure for table `elections`
--

CREATE TABLE `elections` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('upcoming','ongoing','ended') NOT NULL DEFAULT 'upcoming',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `elections`
--

INSERT INTO `elections` (`id`, `title`, `description`, `start_date`, `end_date`, `status`, `created_by`, `created_at`) VALUES
(3, 'COSAA Elections A.Y. 2025–2026', 'Official election for the College of Science Student Organization officers.', '2026-04-03 08:00:00', '2026-06-07 17:00:00', 'ongoing', 1, '2026-04-04 17:30:55');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `election_id`, `title`, `sort_order`) VALUES
(29, 3, 'President', 0),
(30, 3, 'Vice-President Internal', 1),
(31, 3, 'Vice-President External', 2),
(32, 3, 'General Secretary', 3),
(33, 3, 'Deputy Secretary', 4),
(34, 3, 'Treasurer', 5),
(35, 3, 'Auditor', 6),
(36, 3, 'Business Manager', 7),
(37, 3, 'Public Information Officer', 8),
(38, 3, 'BS Biology Representative', 9),
(39, 3, 'BS Computer Science Representative', 10),
(40, 3, 'BS Human Services Representative', 11),
(41, 3, 'BS Psychology Representative', 12),
(42, 3, 'BS Mathematics Representative', 13);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `middle_initial` varchar(5) DEFAULT '',
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `course` varchar(80) NOT NULL,
  `year_level` varchar(20) NOT NULL DEFAULT '1st Year',
  `address` varchar(255) DEFAULT '',
  `profile_pic` varchar(255) DEFAULT '',
  `role` enum('student','admin') NOT NULL DEFAULT 'student',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `id_document` varchar(255) DEFAULT '',
  `verified_at` datetime DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `has_voted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `student_id`, `first_name`, `last_name`, `middle_initial`, `email`, `password_hash`, `course`, `year_level`, `address`, `profile_pic`, `role`, `status`, `id_document`, `verified_at`, `verified_by`, `has_voted`, `created_at`, `updated_at`) VALUES
(1, 'ADM-9901', 'System', 'Admin', '', 'admin@ivote.edu.ph', '$2y$12$jy.hofFyxFfgJ1gidfoQ8O2xUAr3pVijdJb3vn9fkkFkYh9lrtPH2', 'Administration', 'N/A', '', '', 'admin', 'approved', '', NULL, NULL, 0, '2026-04-04 16:50:12', '2026-04-04 16:57:50'),
(2, 'M2023-00946', 'John Edward', 'Villadiego', '', 'm202300946@student.edu.ph', '$2y$12$jy.hofFyxFfgJ1gidfoQ8O2xUAr3pVijdJb3vn9fkkFkYh9lrtPH2', 'BS Computer Science', '3rd Year', '', '/uploads/profiles/student_2_1775386860.PNG', 'student', 'approved', 'documents/cor_2_1775476304.png', '2026-04-06 19:52:17', 1, 1, '2026-04-04 16:56:07', '2026-04-06 19:52:17'),
(3, 'M2023-00945', 'Johny', 'Doe', '', 'm202300945@student.edu.ph', '$2y$12$0c7TDVghz9FQLV9ppUuxX.kB.khNPMTRa3sGzaGC7R8tbOQ91/wY6', 'BS Psychology', '1st Year', '', '', 'student', 'approved', '', '2026-04-04 22:28:37', 1, 0, '2026-04-04 22:28:03', '2026-04-04 22:33:11'),
(4, 'M2023-00947', 'Alyssa', 'Magnayen', '', 'm202300947@student.edu.ph', '$2y$12$bLNEvxFYLrx8tEziPD00b.WQ82thBlcTAlGDxAEq5Kyi6PbM2xOcO', 'BS Biology', '1st Year', '', '', 'student', 'approved', '', '2026-04-05 19:04:08', 1, 1, '2026-04-05 19:02:48', '2026-04-05 19:40:43'),
(5, 'M2023-00949', 'Inka', 'Magindanao', '', 'm202300949@student.edu.ph', '$2y$12$i.Y6CNp3WQTDmo/qHtVduubY8ohS.LzOEbbf2ettkENYm5buuNJFS', 'BS Mathematics', '1st Year', '', '', 'student', 'approved', '', '2026-04-05 20:51:58', 1, 1, '2026-04-05 20:51:26', '2026-04-05 20:54:09'),
(6, 'M2023-00950', 'Mikaela', 'Ariba', '', 'm202300950@student.edu.ph', '$2y$12$mNFheUYkY0iHQJqrAGj7Q.k0ZhMyCURnwlpmI2Iy68JaZ0m/n3ta6', 'BS Psychology', '1st Year', '', '', 'student', 'approved', '', '2026-04-06 18:05:51', 1, 0, '2026-04-06 18:04:58', '2026-04-06 18:05:51'),
(7, 'M2023-00951', 'Chuckie', 'Malindog', '', 'm202300951@student.edu.ph', '$2y$12$qiznlmlM4IRgRhP/NwrIueFTpzV5Qps5/0J2d6wPyJ14kLPPbPamO', 'BS Mathematics', '1st Year', '', '/uploads/profiles/student_7_1775471203.png', 'student', 'approved', '', '2026-04-06 18:22:56', 1, 1, '2026-04-06 18:22:29', '2026-04-06 18:26:43'),
(8, 'M2023-00952', 'Maria', 'Oklis', '', 'm202300952@student.edu.ph', '$2y$12$Y4.wmopY0qMRxwZLTTbMYedeljPIld1Ks05ho9QM8ED9rhOA.A7jO', 'BS Mathematics', '1st Year', '', '', 'student', 'approved', '', '2026-04-06 18:45:16', 1, 1, '2026-04-06 18:45:00', '2026-04-06 18:49:23'),
(9, 'M2023-0953', 'Maria', 'Lorraine', '', 'm20230953@student.edu.ph', '$2y$12$QdVUEz1Fyqf1MZXH/Y5RdODzD/gQf4c7EVx9rDNeKUF4ly9Ovnf/.', 'BS Psychology', '1st Year', '', '', 'student', 'approved', 'documents/cor_9_1775476916.png', '2026-04-06 20:02:40', 1, 0, '2026-04-06 19:52:54', '2026-04-06 20:02:40');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `voter_id` int(11) NOT NULL,
  `voted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `election_id`, `position_id`, `candidate_id`, `voter_id`, `voted_at`) VALUES
(1, 3, 29, 47, 2, '2026-04-04 17:32:36'),
(2, 3, 30, 51, 2, '2026-04-04 17:32:36'),
(3, 3, 31, 54, 2, '2026-04-04 17:32:36'),
(4, 3, 32, 58, 2, '2026-04-04 17:32:36'),
(5, 3, 33, 59, 2, '2026-04-04 17:32:36'),
(6, 3, 34, 63, 2, '2026-04-04 17:32:36'),
(7, 3, 35, 67, 2, '2026-04-04 17:32:36'),
(8, 3, 36, 70, 2, '2026-04-04 17:32:36'),
(9, 3, 37, 72, 2, '2026-04-04 17:32:36'),
(10, 3, 38, 75, 2, '2026-04-04 17:32:36'),
(11, 3, 39, 79, 2, '2026-04-04 17:32:36'),
(12, 3, 40, 80, 2, '2026-04-04 17:32:36'),
(13, 3, 41, 85, 2, '2026-04-04 17:32:36'),
(14, 3, 42, 88, 2, '2026-04-04 17:32:36'),
(15, 3, 29, 46, 3, '2026-04-04 22:30:29'),
(16, 3, 30, 49, 3, '2026-04-04 22:30:49'),
(17, 3, 31, 53, 3, '2026-04-04 22:31:06'),
(18, 3, 32, 56, 3, '2026-04-04 22:31:09'),
(19, 3, 33, 60, 3, '2026-04-04 22:31:13'),
(20, 3, 34, 64, 3, '2026-04-04 22:31:20'),
(21, 3, 35, 66, 3, '2026-04-04 22:31:25'),
(22, 3, 36, 69, 3, '2026-04-04 22:31:29'),
(23, 3, 37, 71, 3, '2026-04-04 22:31:34'),
(24, 3, 38, 75, 3, '2026-04-04 22:31:38'),
(25, 3, 39, 77, 3, '2026-04-04 22:31:41'),
(26, 3, 40, 81, 3, '2026-04-04 22:31:45'),
(27, 3, 41, 83, 3, '2026-04-04 22:31:49'),
(28, 3, 42, 87, 3, '2026-04-04 22:31:53'),
(29, 3, 29, 45, 4, '2026-04-05 19:12:50'),
(30, 3, 30, 50, 4, '2026-04-05 19:21:48'),
(32, 3, 31, 55, 4, '2026-04-05 19:39:49'),
(33, 3, 35, 66, 4, '2026-04-05 19:40:05'),
(34, 3, 36, 68, 4, '2026-04-05 19:40:10'),
(35, 3, 37, 73, 4, '2026-04-05 19:40:14'),
(36, 3, 38, 75, 4, '2026-04-05 19:40:17'),
(37, 3, 39, 77, 4, '2026-04-05 19:44:11'),
(38, 3, 40, 81, 4, '2026-04-05 19:44:16'),
(39, 3, 42, 87, 4, '2026-04-05 19:44:19'),
(40, 3, 41, 85, 4, '2026-04-05 19:44:23'),
(41, 3, 34, 62, 4, '2026-04-05 19:44:30'),
(42, 3, 33, 60, 4, '2026-04-05 19:44:34'),
(43, 3, 32, 56, 4, '2026-04-05 19:44:37'),
(44, 3, 29, 46, 5, '2026-04-05 20:53:06'),
(45, 3, 30, 49, 5, '2026-04-05 20:53:11'),
(46, 3, 31, 54, 5, '2026-04-05 20:53:15'),
(47, 3, 32, 56, 5, '2026-04-05 20:53:21'),
(48, 3, 34, 62, 5, '2026-04-05 20:53:29'),
(49, 3, 35, 65, 5, '2026-04-05 20:53:39'),
(50, 3, 36, 70, 5, '2026-04-05 20:53:42'),
(51, 3, 37, 72, 5, '2026-04-05 20:53:45'),
(52, 3, 38, 74, 5, '2026-04-05 20:53:50'),
(53, 3, 39, 78, 5, '2026-04-05 20:53:54'),
(54, 3, 40, 81, 5, '2026-04-05 20:53:56'),
(55, 3, 41, 83, 5, '2026-04-05 20:54:00'),
(56, 3, 42, 88, 5, '2026-04-05 20:54:04'),
(57, 3, 33, 60, 5, '2026-04-05 20:54:48'),
(58, 3, 29, 45, 6, '2026-04-06 18:06:15'),
(60, 3, 30, 51, 6, '2026-04-06 18:06:21'),
(61, 3, 31, 54, 6, '2026-04-06 18:06:24'),
(62, 3, 32, 56, 6, '2026-04-06 18:06:31'),
(63, 3, 33, 61, 6, '2026-04-06 18:06:34'),
(64, 3, 34, 63, 6, '2026-04-06 18:06:37'),
(65, 3, 35, 66, 6, '2026-04-06 18:06:40'),
(66, 3, 36, 70, 6, '2026-04-06 18:06:44'),
(67, 3, 37, 71, 6, '2026-04-06 18:06:46'),
(68, 3, 38, 75, 6, '2026-04-06 18:06:50'),
(69, 3, 39, 77, 6, '2026-04-06 18:06:53'),
(70, 3, 40, 82, 6, '2026-04-06 18:06:55'),
(71, 3, 41, 83, 6, '2026-04-06 18:06:58'),
(72, 3, 42, 88, 6, '2026-04-06 18:07:03'),
(73, 3, 29, 45, 7, '2026-04-06 18:23:21'),
(74, 3, 30, 51, 7, '2026-04-06 18:23:24'),
(75, 3, 31, 53, 7, '2026-04-06 18:23:27'),
(76, 3, 32, 57, 7, '2026-04-06 18:23:29'),
(77, 3, 33, 61, 7, '2026-04-06 18:23:32'),
(78, 3, 34, 62, 7, '2026-04-06 18:23:34'),
(79, 3, 36, 69, 7, '2026-04-06 18:23:40'),
(80, 3, 38, 76, 7, '2026-04-06 18:23:48'),
(81, 3, 29, 46, 8, '2026-04-06 18:45:45'),
(82, 3, 42, 86, 8, '2026-04-06 18:48:52'),
(83, 3, 37, 71, 8, '2026-04-06 18:48:56'),
(84, 3, 36, 69, 8, '2026-04-06 18:48:59'),
(85, 3, 31, 55, 8, '2026-04-06 18:49:07'),
(86, 3, 32, 56, 8, '2026-04-06 18:49:10'),
(87, 3, 33, 60, 8, '2026-04-06 18:49:12'),
(88, 3, 34, 62, 8, '2026-04-06 18:49:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `election_id` (`election_id`),
  ADD KEY `position_id` (`position_id`);

--
-- Indexes for table `elections`
--
ALTER TABLE `elections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `election_id` (`election_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_voter_position` (`voter_id`,`position_id`),
  ADD KEY `election_id` (`election_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `elections`
--
ALTER TABLE `elections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `candidates_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `elections`
--
ALTER TABLE `elections`
  ADD CONSTRAINT `elections_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `positions_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_3` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_4` FOREIGN KEY (`voter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
