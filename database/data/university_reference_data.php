<?php

declare(strict_types=1);

/**
 * Two layers:
 *
 * 1) **Units (named fund sources)** — Colleges + Non-College/Services: each `funds` entry becomes a `fund_sources` row.
 * 2) **Offices (no unit-level fund list)** — GASS, Support, and operational offices: `funds => []` becomes one
 *    **General / Operating Fund** per department (still a row in `fund_sources`, tied to that office).
 *
 * College order below matches the UNITS section of the master list.
 *
 * @return list<array{name: string, abbreviation: ?string, funds: list<string>}>
 */
return [
    // —— GENERAL ADMINISTRATIVE & SUPPORT SERVICES (offices → General / Operating Fund) ——
    ['name' => 'Board of Regents (BOR)', 'abbreviation' => 'BOR', 'funds' => []],
    ['name' => 'Office of the Board Secretary (OBS)', 'abbreviation' => 'OBS', 'funds' => []],
    ['name' => 'Office of the President', 'abbreviation' => 'OOP', 'funds' => []],
    ['name' => 'Commission on Audit (COA)', 'abbreviation' => 'COA', 'funds' => []],
    ['name' => 'Presidential Management Office (PMO)', 'abbreviation' => 'PMO', 'funds' => []],
    ['name' => 'University Planning and Development Office (UPDO)', 'abbreviation' => 'UPDO', 'funds' => []],
    ['name' => 'University Information Office (UIO)', 'abbreviation' => 'UIO', 'funds' => []],
    ['name' => 'International Affairs & External Linkages Office (IAELO)', 'abbreviation' => 'IAELO', 'funds' => []],
    ['name' => 'Internal Audit Office (IAO)', 'abbreviation' => 'IAO', 'funds' => []],
    ['name' => 'University Legal Office (ULO)', 'abbreviation' => 'ULO', 'funds' => []],
    ['name' => 'Vice President for Finance and Administration (VPFAD)', 'abbreviation' => 'VPFAD', 'funds' => []],
    ['name' => 'Administrator, University Hospital (UNPH)', 'abbreviation' => 'UNPH', 'funds' => []],
    ['name' => 'Information & Communications Technology Office (ICTO)', 'abbreviation' => 'ICTO', 'funds' => []],
    ['name' => 'ICT Maintenance Office', 'abbreviation' => 'ICTMO', 'funds' => []],
    ['name' => 'Information System Development & Security Office', 'abbreviation' => 'ISDSO', 'funds' => []],
    ['name' => 'Financial Services Office (FSO)', 'abbreviation' => 'FSO', 'funds' => []],
    ['name' => 'Accounting Office', 'abbreviation' => 'ACCT', 'funds' => []],
    ['name' => 'Budgeting Office', 'abbreviation' => 'BUDG', 'funds' => []],
    ['name' => 'Cashiering Office', 'abbreviation' => 'CASH', 'funds' => []],
    ['name' => 'Administrative Services Office (CAO)', 'abbreviation' => 'CAO', 'funds' => []],
    ['name' => 'Human Resource Management Office (HRMO)', 'abbreviation' => 'HRMO', 'funds' => []],
    ['name' => 'Supply & Property Management Office (SPMO)', 'abbreviation' => 'SPMO', 'funds' => []],
    ['name' => 'Records Office', 'abbreviation' => 'RECO', 'funds' => []],
    ['name' => 'Procurement Office / BAC', 'abbreviation' => 'BAC', 'funds' => []],
    ['name' => 'General Services Office (GSO)', 'abbreviation' => 'GSO', 'funds' => []],
    ['name' => 'Building and Water Maintenance Unit (BWMU)', 'abbreviation' => 'BWMU', 'funds' => []],
    ['name' => 'Transport and Motorpool Services Office (TMSO)', 'abbreviation' => 'TMSO', 'funds' => []],
    ['name' => 'Infrastructure Project & Management Office (IPMO)', 'abbreviation' => 'IPMO', 'funds' => []],
    ['name' => 'Project Implementation Office', 'abbreviation' => 'PIO', 'funds' => []],
    ['name' => 'Planning & Design Office', 'abbreviation' => 'PDO', 'funds' => []],
    ['name' => 'Monitoring & Evaluation Office', 'abbreviation' => 'MEO', 'funds' => []],
    ['name' => 'Special Assistant on Administrative Matters', 'abbreviation' => 'SAAM', 'funds' => []],
    ['name' => 'Instruction & Faculty Development Office (IFDO)', 'abbreviation' => 'IFDO', 'funds' => []],
    ['name' => "Registrar's Office", 'abbreviation' => 'REG', 'funds' => []],
    ['name' => 'Quality Management Office- ISO', 'abbreviation' => 'QMO', 'funds' => []],
    ['name' => 'Library Services Office', 'abbreviation' => 'LSO', 'funds' => []],
    ['name' => 'Center for Gender and Development', 'abbreviation' => 'CGAD', 'funds' => []],
    ['name' => 'Laboratory Services Office (SLSO)', 'abbreviation' => 'SLSO', 'funds' => []],
    ['name' => 'National Service Training Porgram Office', 'abbreviation' => 'NSTP', 'funds' => []],
    ['name' => 'UNP-Nasaririt Multimedia Studio', 'abbreviation' => 'UNMS', 'funds' => []],
    ['name' => 'Office of Student Affairs & Services', 'abbreviation' => 'OSAS', 'funds' => []],
    ['name' => 'Museo de la Universidad', 'abbreviation' => 'MUSEO', 'funds' => []],

    // —— SUPPORT TO OPERATIONS (offices → General / Operating Fund) ——
    ['name' => 'Vice President for Finance & Administration (VPFAD)', 'abbreviation' => 'VPFAD', 'funds' => []],
    ['name' => 'Production & Auxiliary Services (PASO)', 'abbreviation' => 'PASO', 'funds' => []],
    ['name' => 'Medical / Dental Services (MDSO)', 'abbreviation' => 'MDSO', 'funds' => []],
    ['name' => 'Enterprise & Development Office (EDO)', 'abbreviation' => 'EDO', 'funds' => []],
    ['name' => 'Environmental Management Office (EMO)', 'abbreviation' => 'EMO', 'funds' => []],

    // —— OPERATIONS: organizational offices (not the college “units” below) ——
    ['name' => 'Higher Education Services', 'abbreviation' => 'HES', 'funds' => []],
    ['name' => 'Advance Education', 'abbreviation' => 'ADVED', 'funds' => [
        'Accountancy', 'MAN', 'MOS', 'MSJCJSC',
    ]],
    ['name' => 'Research Services', 'abbreviation' => 'RSVC', 'funds' => []],
    ['name' => 'Vice President for Research & Extension (VPRE)', 'abbreviation' => 'VPRE', 'funds' => []],
    ['name' => 'Director, Research Services (URDO)', 'abbreviation' => 'URDO', 'funds' => []],
    ['name' => 'Extension Services (UEO)', 'abbreviation' => 'UEO', 'funds' => []],

    // VPAA sits over colleges; still one office row (General / Operating Fund) for non-unit budgeting if needed
    ['name' => 'Vice President for Academic Affairs (VPAA)', 'abbreviation' => 'VPAA', 'funds' => []],

    // —— UNITS: colleges (named fund sources per UNITS table) ——
    ['name' => 'College of Business Administration and Accountancy(CBAA)', 'abbreviation' => 'CBAA', 'funds' => [
        'BSCM', 'Computer', 'ENTREP', 'FM', 'GS Compre Fee', 'HRDM', 'Internet',
    ]],
    ['name' => 'College of Arts and Sciences(CAS)', 'abbreviation' => 'CAS', 'funds' => [
        'Compre Exam', 'DPE Athletic Fund', 'OJT BA Comm', 'OJT BS Bio', 'OJT BS EnviSci Fund', 'OJT BS Psych',
    ]],
    ['name' => 'College of Communication and Information Technology(CCIT)', 'abbreviation' => 'CCIT', 'funds' => [
        'BSCS', 'Internet Fund', 'MAIN', 'OJT BLIS', 'OJT BSIT',
    ]],
    ['name' => 'College of Criminal Justice Education(CCJE)', 'abbreviation' => 'CCJE', 'funds' => [
        'Compre', 'Internship',
    ]],
    ['name' => 'College of Fine Arts and Design(CFAD)', 'abbreviation' => 'CFAD', 'funds' => ['ARTE']],
    ['name' => 'College of Health Sciences(CHS)', 'abbreviation' => 'CHS', 'funds' => ['CWF']],
    ['name' => 'College of Hospitality and Tourism Management(CHTM)', 'abbreviation' => 'CHTM', 'funds' => [
        'Affiliation-Tourism', 'Laboratory Fund', 'OJT-BSHM/OJT-HRA',
    ]],
    ['name' => 'College of Law(CLAW)', 'abbreviation' => 'CLAW', 'funds' => []],
    ['name' => 'College of Medicine(CMED)', 'abbreviation' => 'CMED', 'funds' => [
        'AFFILIATION', 'CLERKSHIP', 'MEDICINE', 'REVALIDA',
    ]],
    ['name' => 'College of Nursing(CN)', 'abbreviation' => 'CN', 'funds' => [
        'Affiliation Fee', 'Compre', 'RLE',
    ]],
    ['name' => 'College of Engineering(COE)', 'abbreviation' => 'COE', 'funds' => ['CAE Fund', 'OJT Fund']],
    ['name' => 'College of Public Administration(CPAD)', 'abbreviation' => 'CPAD', 'funds' => [
        'Compre Exam Fee', 'Internship',
    ]],
    ['name' => 'College of Social Work(CSW)', 'abbreviation' => 'CSW', 'funds' => ['Field Observation Fee']],
    ['name' => 'College of Teacher Education(CTE)', 'abbreviation' => 'CTE', 'funds' => [
        'Compre', 'EdTech', 'LS Basic Education', 'LS Childminding', 'LS SHS', 'PT',
    ]],
    ['name' => 'College of Technology(CTECH)', 'abbreviation' => 'CTECH', 'funds' => ['SHOP FEE']],
    ['name' => 'College of Architecture(CARCH)', 'abbreviation' => 'CARCH', 'funds' => []],

    // —— UNITS: shared non-college funds ——
    ['name' => 'Non-College / Services', 'abbreviation' => 'NCOL', 'funds' => [
        'Admission Services',
        'CAP - Cultural Fund',
        'CODE',
        'GUIDANCE',
        'IAELO - BESFS Thai',
        'LSO - LIBRARY FUND',
        'MDSO - MEDICAL DENTAL FUND',
        'NEW TANDEM',
        'NSTP',
        'OSAS - SHDS',
        'ROTC',
        'SC - SMF',
        'SDP-SCUAA Fund',
        'SLSO - Laboratory Fund',
    ]],
];
