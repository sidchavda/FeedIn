@extends('layouts.custom-master1')

@section('styles')
    <style>
        :root {
            --primary-color: #2563eb;
            --text-main: #1f2937;
            --text-muted: #4b5563;
            --bg-light: #f9fafb;
            --border-color: #e5e7eb;
        }

        .policy-wrapper {
            background-color: var(--bg-light);
            padding: 60px 20px;
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .policy-card {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            padding: 50px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .policy-header {
            text-align: center;
            margin-bottom: 50px;
            border-bottom: 2px solid var(--bg-light);
            padding-bottom: 30px;
        }

        .policy-header h1 {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.025em;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .last-updated-badge {
            display: inline-block;
            background: #eff6ff;
            color: var(--primary-color);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .policy-section {
            margin-bottom: 35px;
        }

        .policy-section h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .policy-section h2 span {
            background: var(--primary-color);
            color: white;
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            margin-right: 12px;
            font-size: 0.9rem;
        }

        .policy-content p {
            line-height: 1.7;
            color: var(--text-muted);
            margin-bottom: 15px;
            font-size: 1.05rem;
        }

        .policy-content ul {
            list-style: none;
            padding-left: 40px;
            margin-bottom: 20px;
        }

        .policy-content ul li {
            position: relative;
            line-height: 1.7;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .policy-content ul li::before {
            content: "•";
            color: var(--primary-color);
            font-weight: bold;
            position: absolute;
            left: -20px;
        }

        .sub-heading {
            font-weight: 600;
            color: var(--text-main);
            margin-top: 20px;
            margin-bottom: 10px;
            display: block;
        }

        .contact-box {
            background: var(--bg-light);
            border-left: 4px solid var(--primary-color);
            padding: 20px;
            margin-top: 30px;
            border-radius: 0 8px 8px 0;
        }

        @media (max-width: 768px) {
            .policy-card { padding: 30px 20px; }
            .policy-header h1 { font-size: 1.75rem; }
        }
    </style>
@endsection

@section('content')
<div class="policy-wrapper">
    <div class="policy-card">
        <div class="policy-header">
            <h1>Privacy Policy</h1>
            <div class="last-updated-badge">
                Last updated: {{ date('F j, Y') }}
            </div>
        </div>

        <div class="policy-content">
            <!-- Section 1 -->
            <div class="policy-section">
                <h2><span>1</span> GENERAL</h2>
                <p>Feedin (“Feedin”, “We”, “Our”, “Us”) respects and protects the information provided by users (“You”, “Your”, “User”) that can identify you personally (“Personal Information”). By using the Feedin mobile application (“App”), you agree to the collection, storage, and use of your Personal Information as described in this Privacy Policy (“Privacy Policy”).</p>
                <p>This Privacy Policy applies to all users who access the App. You should read and understand this policy before sharing any Personal Information.</p>
                <p>We value your privacy and are committed to protecting it while providing a better and personalized experience. Access to the App content is only allowed if you accept this Privacy Policy along with our terms and conditions (“Terms”).</p>
            </div>

            <!-- Section 2 -->
            <div class="policy-section">
                <h2><span>2</span> INFORMATION COLLECTED</h2>
                <span class="sub-heading">Traffic Data Collected</span>
                <p>To provide and improve the App, we automatically collect certain information when you use it, such as IP addresses, domain details, and device interactions. This is referred to as “Traffic Data”.</p>
                
                <span class="sub-heading">Personal Information Collected</span>
                <p>To use some features, we may ask for:</p>
                <ul>
                    <li>Contact details (email address, phone number, contact list if permitted)</li>
                    <li>Device details and basic information (time zone, address, and location)</li>
                </ul>
                <p>Your Personal Information may be stored on servers outside India. We delete your data within 180 days after you uninstall the App or delete your account, unless required otherwise for legal reasons.</p>
                
                <span class="sub-heading">Third-Party Links</span>
                <p>Our App may include links to other platforms. We do not control these third-party platforms and are not responsible for their privacy practices. We recommend reading their policies before sharing data.</p>
            </div>

            <!-- Section 3 -->
            <div class="policy-section">
                <h2><span>3</span> USAGE OF PERSONAL INFORMATION</h2>
                <p>The information collected is used to provide and improve services, fulfill responsibilities, and communicate updates or promotions via calls, messages, or emails.</p>
                <p>We may use your Personal Information for "Permitted Use," which includes:</p>
                <ul>
                    <li>Creating marketing profiles and business planning</li>
                    <li>Managing relationships with partners and advertisers</li>
                    <li>Monitoring usage to improve overall user experience</li>
                </ul>
            </div>

            <!-- Section 4 -->
            <div class="policy-section">
                <h2><span>4</span> SHARING OF INFORMATION</h2>
                <p>In cases of mergers, asset sales, or business transfers, your Personal Information may be shared with the involved third party. We may also disclose information to:</p>
                <ul>
                    <li>Comply with legal obligations or court orders</li>
                    <li>Protect the rights, property, or safety of Feedin</li>
                    <li>Enforce our Terms and respond to legal claims</li>
                </ul>
            </div>

            <!-- Section 5 -->
            <div class="policy-section">
                <h2><span>5</span> SECURITY</h2>
                <p>We take appropriate technical and administrative safeguards to protect your data. However, no system is completely secure. If a breach occurs, we will make reasonable efforts to inform you as allowed by law.</p>
                <div class="contact-box">
                    <strong>Security Alert:</strong> Never share your password. If your account is compromised, change it immediately and contact us at <strong>inquiry.feedin@gmail.com</strong>.
                </div>
            </div>

            <!-- Section 6 -->
            <div class="policy-section">
                <h2><span>6</span> UPDATES AND CHANGES</h2>
                <p>We may update this Privacy Policy at any time. Continued use of the App after updates constitutes your acceptance of the revised policy.</p>
            </div>

            <!-- Section 7 -->
            <div class="policy-section">
                <h2><span>7</span> YOUR RIGHTS</h2>
                <p>You have the right to review and correct your Personal Information. You may also request the deletion of your data by writing to <strong>inquiry.feedin@gmail.com</strong>. Note that withdrawing consent may limit our ability to provide certain services.</p>
            </div>

            <!-- Section 8 -->
            <div class="policy-section">
                <h2><span>8</span> LIMITATION OF LIABILITY</h2>
                <p>Feedin does not guarantee the accuracy or reliability of content on the App. We do not provide warranties for non-infringement or fitness for a particular purpose, nor do we guarantee the App is free from harmful elements like viruses.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection