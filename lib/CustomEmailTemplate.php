<?php
/**
 * Custom Email Template voor The Good Cloud
 *
 * Deze klasse extend de standaard Nextcloud email template
 * en overschrijft specifiek de welkomstmail en verificatie-email
 */

namespace OCA\CustomEmailTemplate;

use OC\Mail\EMailTemplate;

class CustomEmailTemplate extends EMailTemplate {

    protected $isWelcomeEmail = false;
    protected $isVerificationEmail = false;
    protected $serverUrl = '';
    protected $verificationCode = '';
    protected $verificationUrl = '';

    public function __construct($defaults, $urlGenerator, $l10n, $config) {
        parent::__construct($defaults, $urlGenerator, $l10n, $config);

        $this->serverUrl = $this->themingDefaults->getBaseUrl();
    }

    /**
     * Overschrijf setSubject om te detecteren welk type email dit is
     */
    public function setSubject(string $subject): void {
        // Detecteer verificatie-email
        $verificationKeywords = ['verif', 'registrat', 'confirm', 'code'];
        foreach ($verificationKeywords as $keyword) {
            if (stripos($subject, $keyword) !== false) {
                $this->isVerificationEmail = true;
                $subject = 'Verify your Good Cloud workspace';
                parent::setSubject($subject);
                return;
            }
        }

        // Detecteer welkomstmail
        $welcomeKeywords = ['welcome', 'welkom', 'aboard'];
        foreach ($welcomeKeywords as $keyword) {
            if (stripos($subject, $keyword) !== false) {
                $this->isWelcomeEmail = true;
                $subject = 'Welcome to The Good Cloud';
                break;
            }
        }
        parent::setSubject($subject);
    }

    /**
     * Overschrijf addBodyText om verificatiecode te vangen
     */
    public function addBodyText(string $text, $plainText = ''): void {
        // Probeer verificatiecode uit de tekst te halen
        if ($this->isVerificationEmail && empty($this->verificationCode)) {
            // Zoek naar patronen zoals "code: XXXXX" of "Verificatiecode: XXXXX"
            if (preg_match('/(?:code|verificatiecode)[:\s]+([A-Za-z0-9]+)/i', $text, $matches)) {
                $this->verificationCode = $matches[1];
            }
            // Zoek ook naar standalone codes (meestal 6-10 karakters, alfanumeriek)
            if (empty($this->verificationCode) && preg_match('/\b([A-Z0-9]{6,10})\b/', $text, $matches)) {
                $this->verificationCode = $matches[1];
            }
        }
        parent::addBodyText($text, $plainText);
    }

    /**
     * Overschrijf addBodyButton om verificatie URL en code te vangen
     */
    public function addBodyButton(string $text, string $url, string $plainText = ''): void {
        if ($this->isVerificationEmail) {
            if (empty($this->verificationUrl)) {
                $this->verificationUrl = $url;
            }
            // Haal verificatiecode uit URL als fallback (staat vaak aan het einde)
            // URL format: .../register/TOKEN/CODE
            if (empty($this->verificationCode) && preg_match('/\/([A-Za-z0-9]{6,12})$/', $url, $matches)) {
                $this->verificationCode = $matches[1];
            }
        }
        parent::addBodyButton($text, $url, $plainText);
    }

    /**
     * Genereer de volledige HTML voor de welkomstmail
     */
    protected function getWelcomeEmailHtml(): string {
        $logoUrl = $this->themingDefaults->getLogo();
        $serverUrl = $this->serverUrl;

        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to The Good Cloud</title>
</head>
<body style="margin: 0; padding: 0; background-color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #ffffff;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width: 600px;">
                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding-bottom: 30px;">
                            <img src="' . htmlspecialchars($logoUrl) . '" alt="The Good Cloud" style="max-width: 180px; height: auto; border-radius: 50%;">
                        </td>
                    </tr>

                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding-bottom: 30px;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: 600; color: #1a1a1a; line-height: 1.3;">
                                Your private cloud is ready
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 0 20px;">
                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #333333;">
                                Welcome aboard! Your files now have a home that\'s truly yours.
                            </p>

                            <p style="margin: 0 0 15px; font-size: 16px; line-height: 1.6; color: #333333;">
                                With your free trial, you get:
                            </p>

                            <ul style="margin: 0 0 20px; padding-left: 20px; font-size: 16px; line-height: 1.8; color: #333333;">
                                <li>2 GB private cloud storage</li>
                                <li>Secure, hassle-free access</li>
                                <li>Hosted in Europe — powered by 100% green energy</li>
                            </ul>

                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #333333;">
                                Your free account is valid for 180 days. You can upgrade to a paid plan at any time — simply go to your account and click "Manage subscription".
                            </p>

                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #333333;">
                                The Good Cloud is the European alternative to Big Tech tools — so you can store, share and collaborate with total privacy and full ownership of your data.
                            </p>

                            <p style="margin: 0 0 30px; font-size: 16px; line-height: 1.6; color: #333333;">
                                Log in, upload your first files, and see how easy full privacy can be.
                            </p>

                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #333333;">
                                — The Good Cloud Team
                            </p>
                        </td>
                    </tr>

                    <!-- CTA Button -->
                    <tr>
                        <td align="center" style="padding: 10px 20px 40px;">
                            <a href="' . htmlspecialchars($serverUrl) . '" style="display: inline-block; padding: 14px 32px; background-color: #3b7bbf; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600; border-radius: 4px;">
                                Go to my account
                            </a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 30px 20px; border-top: 1px solid #e5e5e5;">
                            <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #888888;">
                                The Good Cloud - You are not the product<br>
                                This is an automatically sent email, please do not reply.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    /**
     * Genereer de volledige HTML voor de verificatie-email
     */
    protected function getVerificationEmailHtml(): string {
        $logoUrl = $this->themingDefaults->getLogo();
        $verificationCode = htmlspecialchars($this->verificationCode);
        $verificationUrl = htmlspecialchars($this->verificationUrl ?: $this->serverUrl);

        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your email - The Good Cloud</title>
</head>
<body style="margin: 0; padding: 0; background-color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #ffffff;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width: 600px;">
                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding-bottom: 30px;">
                            <img src="' . htmlspecialchars($logoUrl) . '" alt="The Good Cloud" style="max-width: 180px; height: auto; border-radius: 50%;">
                        </td>
                    </tr>

                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding-bottom: 30px;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: 600; color: #1a1a1a; line-height: 1.3;">
                                Registration
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 0 20px;">
                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #333333;">
                                Hi there,
                            </p>

                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #333333;">
                                Thanks for choosing The Good Cloud.
                            </p>

                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #333333;">
                                Before we activate your workspace, please verify your account:
                            </p>

                            <p style="margin: 0 0 25px; font-size: 16px; line-height: 1.6; color: #333333;">
                                <strong>Verification code:</strong> <span style="font-family: monospace; font-size: 18px; background-color: #f5f5f5; padding: 4px 8px; border-radius: 4px;">' . $verificationCode . '</span>
                            </p>
                        </td>
                    </tr>

                    <!-- CTA Button -->
                    <tr>
                        <td align="center" style="padding: 10px 20px 30px;">
                            <a href="' . $verificationUrl . '" style="display: inline-block; padding: 14px 32px; background-color: #3b7bbf; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600; border-radius: 4px;">
                                Complete registration
                            </a>
                        </td>
                    </tr>

                    <!-- Additional text -->
                    <tr>
                        <td style="padding: 0 20px;">
                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #333333;">
                                Once confirmed, your workspace will be ready to use.
                            </p>

                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #666666;">
                                If you didn\'t request this, you can simply ignore this message.
                            </p>

                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #333333;">
                                — The Good Cloud Team
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 30px 20px; border-top: 1px solid #e5e5e5;">
                            <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #888888;">
                                The Good Cloud - You are not the product<br>
                                This is an automatically sent email, please do not reply.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    /**
     * Genereer plain text versie van verificatie-email
     */
    protected function getVerificationEmailPlainText(): string {
        $verificationCode = $this->verificationCode;
        $verificationUrl = $this->verificationUrl ?: $this->serverUrl;

        return "Registration - The Good Cloud

Hi there,

Thanks for choosing The Good Cloud.

Before we activate your workspace, please verify your account:

Verification code: {$verificationCode}

Complete registration: {$verificationUrl}

Once confirmed, your workspace will be ready to use.

If you didn't request this, you can simply ignore this message.

— The Good Cloud Team

--
The Good Cloud - You are not the product
This is an automatically sent email, please do not reply.
";
    }

    /**
     * Genereer plain text versie van welkomstmail
     */
    protected function getWelcomeEmailPlainText(): string {
        $serverUrl = $this->serverUrl;

        return "Welcome to The Good Cloud

Your private cloud is ready

Welcome aboard! Your files now have a home that's truly yours.

With your free trial, you get:
• 2 GB private cloud storage
• Secure, hassle-free access
• Hosted in Europe — powered by 100% green energy

Your free account is valid for 180 days. You can upgrade to a paid plan at any time — simply go to your account and click \"Manage subscription\".

The Good Cloud is the European alternative to Big Tech tools — so you can store, share and collaborate with total privacy and full ownership of your data.

Log in, upload your first files, and see how easy full privacy can be.

— The Good Cloud Team

Go to my account: {$serverUrl}

--
The Good Cloud - You are not the product
This is an automatically sent email, please do not reply.
";
    }

    /**
     * Overschrijf renderHtml om custom emails te tonen
     */
    public function renderHtml(): string {
        if ($this->isVerificationEmail) {
            return $this->getVerificationEmailHtml();
        }
        if ($this->isWelcomeEmail) {
            return $this->getWelcomeEmailHtml();
        }
        return parent::renderHtml();
    }

    /**
     * Overschrijf renderText om custom emails te tonen
     */
    public function renderText(): string {
        if ($this->isVerificationEmail) {
            return $this->getVerificationEmailPlainText();
        }
        if ($this->isWelcomeEmail) {
            return $this->getWelcomeEmailPlainText();
        }
        return parent::renderText();
    }
}
