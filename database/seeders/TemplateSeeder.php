<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                "id" => 1,
                "title" => "Company Registration Email",
                "description" => "<div class=\"m_-4718867701997331129header\" style=\"text-align: center; font-weight: bold; color: rgb(61, 34, 181); margin-bottom: 20px; font-family: Arial, Helvetica, sans-serif;\"><div class=\"m_-4718867701997331129header\" style=\"font-size: 24px; margin-bottom: 20px; font-family: Arial, Helvetica, sans-serif;\">Company Registration Details</div><div class=\"m_-4718867701997331129content\" style=\"color: rgb(34, 34, 34); font-family: Arial, Helvetica, sans-serif; font-weight: 400; text-align: start;\"><p style=\"font-size: 14px; line-height: 1.6; margin: 16px 0px;\">Hello,</p><p style=\"font-size: 14px; line-height: 1.6; margin: 16px 0px;\">Congratulations! Your Company has been successfully onboarded. Below are your account details:</p><div class=\"m_-4718867701997331129detail\" style=\"font-size: small; margin: 12px 0px;\"><strong style=\"display: inline-block; width: 120px;\">Company Name:</strong>&nbsp;#name#</div><div class=\"m_-4718867701997331129detail\" style=\"font-size: small; margin: 12px 0px;\"><strong style=\"display: inline-block; width: 120px;\">Company Code:</strong>&nbsp;#company-code#</div><div class=\"m_-4718867701997331129detail\" style=\"font-size: small; margin: 12px 0px;\"><strong style=\"display: inline-block; width: 120px;\">URL:</strong>&nbsp;#url#</div><div class=\"m_-4718867701997331129detail\" style=\"font-size: small; margin: 12px 0px;\"><strong style=\"display: inline-block; width: 120px;\">Email:</strong>&nbsp;#email#</div><div class=\"m_-4718867701997331129detail\" style=\"font-size: small; margin: 12px 0px;\"><strong style=\"display: inline-block; width: 120px;\">Password:</strong>&nbsp;#password#</div><a href=\"#url#\" class=\"m_-4718867701997331129btn-login\" target=\"_blank\" data-saferedirecturl=\"https://www.google.com/url?q=https://dashboard.engagereward.com/facebp&amp;source=gmail&amp;ust=1773316513603000&amp;usg=AOvVaw0sn_vAfVq9jA_a1rPSOQ5d\" style=\"font-size: small; color: white; display: inline-block; background-color: rgb(58, 45, 211); padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px;\">LOGIN TO DASHBOARD</a><p style=\"font-size: 14px; line-height: 1.6; margin: 16px 0px;\">Please make sure to change your password after your first login for security purposes.</p><p style=\"line-height: 1.6; margin: 16px 0px; color: rgb(0, 0, 0) !important;\"><span style=\"font-size: 14px;\">Didn’t request this? Just ignore this email or contact us at&nbsp;</span><span style=\"font-size: 12.96px;\">&nbsp;<a href=\"mailto:#support-mail#\" target=\"_blank\">#support-mail#</a></span></p><p style=\"font-size: 14px; line-height: 1.6; margin: 16px 0px;\">Thank you,<br>#app-name# Team</p></div></div>",
                "subtitle" => "",
                "is_active" => 1,
                "is_delete" => 0,
                "created_at" => "2026-03-11 17:32:07",
                "updated_at" => "2026-03-11 18:23:54",
                "deleted_at" => null
            ],
            [
                "id" => 2,
                "title" => "2x Like",
                "description" => "<p>+ #point#  points for your like! Keep it going! </p>",
                "subtitle" => "2X Points Unlocked! 🚀",
                "is_active" => 1,
                "is_delete" => 0,
                "created_at" => "2026-03-11 22:13:50",
                "updated_at" => "2026-03-12 12:51:18",
                "deleted_at" => null
            ],
            [
                "id" => 3,
                "title" => "2X Share",
                "description" => "<p><span style=\"font-size: 12.96px;\">+ #point# points for sharing. Great contribution!</span><span style=\"font-size: 12.96px;\"> </span></p>",
                "subtitle" => "2X Points Unlocked! 🚀",
                "is_active" => 1,
                "is_delete" => 0,
                "created_at" => "2026-03-11 22:16:59",
                "updated_at" => "2026-03-12 13:34:55",
                "deleted_at" => null
            ],
            [
                "id" => 4,
                "title" => "comment",
                "description" => "<p>+ #point#&nbsp; points for your commenting. Great contribution!</p>",
                "subtitle" => "",
                "is_active" => 1,
                "is_delete" => 0,
                "created_at" => "2026-03-11 22:22:16",
                "updated_at" => "2026-03-11 22:22:16",
                "deleted_at" => null
            ],
            [
                "id" => 5,
                "title" => "Invitation Email",
                "description" => "<p style=\"line-height: 1.5; margin-top: 1rem; margin-right: 0px; margin-left: 0px; color: rgb(51, 51, 51); font-size: 16px;\">Hi #name#,</p><p style=\"line-height: 1.5; margin-top: 1rem; margin-right: 0px; margin-left: 0px; color: rgb(51, 51, 51); font-size: 16px;\">You’ve been exclusively invited to join #app-name# — the platform that turns your social engagement into real rewards.</p><p style=\"line-height: 1.5; margin-top: 1rem; margin-right: 0px; margin-left: 0px; color: rgb(51, 51, 51); font-size: 16px;\">🔑 Your invitation code : #invitation-code#</p><p style=\"line-height: 1.5; margin-top: 1rem; margin-right: 0px; margin-left: 0px; color: rgb(51, 51, 51); font-size: 16px;\">📲 Download the&nbsp;<b style=\"line-height: 1.5;\">#app-name#</b>&nbsp;app and enter your code to get started:</p><p style=\"line-height: 1.5; margin-top: 1rem; margin-right: 0px; margin-left: 0px; color: rgb(51, 51, 51); font-size: 16px;\"></p><p style=\"line-height: 1.5; margin-top: 1rem; margin-right: 0px; margin-left: 0px; color: rgb(51, 51, 51); font-size: 16px;\">Start engaging. Start earning.<br></p><p style=\"line-height: 1.5; margin-top: 1rem; margin-right: 0px; margin-left: 0px; color: rgb(51, 51, 51); font-size: 16px;\">#app_store_play_store_button#</p><div style=\"line-height: 1.5; color: rgb(51, 51, 51); font-size: 16px;\">Cheers,</div><p><span style=\"color: rgb(51, 51, 51); font-size: 16px;\"></span></p><div style=\"line-height: 1.5; color: rgb(51, 51, 51); font-size: 16px;\">#app-name# 💙</div><div style=\"line-height: 1.5; color: rgb(51, 51, 51); font-size: 16px;\"><br></div>",
                "subtitle" => "🎉 You're Invited to Join Engage Reward!",
                "is_active" => 1,
                "is_delete" => 0,
                "created_at" => "2026-03-11 22:24:30",
                "updated_at" => "2026-03-12 19:19:05",
                "deleted_at" => null
            ],
            [
                "id" => 6,
                "title" => "1x Like",
                "description" => "<p><span style=\"font-size: 12.96px;\">+ #point#&nbsp; points for your like! Keep it going!&nbsp;</span></p>",
                "subtitle" => "Points Unlocked!",
                "is_active" => 1,
                "is_delete" => 0,
                "created_at" => "2026-03-12 12:52:35",
                "updated_at" => "2026-03-12 12:52:35",
                "deleted_at" => null
            ],
            [
                "id" => 7,
                "title" => "1X Share",
                "description" => "<p><span style=\"font-size: 12.96px;\">+ #point# points for sharing. Great contribution!</span><span style=\"font-size: 12.96px;\">&nbsp;</span></p>",
                "subtitle" => "Points Unlocked!",
                "is_active" => 1,
                "is_delete" => 0,
                "created_at" => "2026-03-12 13:35:32",
                "updated_at" => "2026-03-12 13:35:32",
                "deleted_at" => null
            ],
            [
                "id" => 8,
                "title" => "1X Comment",
                "description" => "<p><span style=\"font-size: 12.96px;\">+ #point# points for commenting. Great contribution!</span><span style=\"font-size: 12.96px;\">&nbsp;</span></p>",
                "subtitle" => "Points Unlocked! 🚀",
                "is_active" => 1,
                "is_delete" => 0,
                "created_at" => "2026-03-12 13:36:33",
                "updated_at" => "2026-03-12 13:36:33",
                "deleted_at" => null
            ],
            [
                "id" => 9,
                "title" => "2X Comment",
                "description" => "<p><span style=\"font-size: 12.96px;\">+ #point# points for commenting. Great contribution!</span><span style=\"font-size: 12.96px;\">&nbsp;</span></p>",
                "subtitle" => "2X Points Unlocked! 🚀",
                "is_active" => 1,
                "is_delete" => 0,
                "created_at" => "2026-03-12 13:39:58",
                "updated_at" => "2026-03-12 13:39:58",
                "deleted_at" => null
            ],
            [
                "id" => 10,
                "title" => "Post Notification",
                "description" => "<p data-pm-slice=\"1 1 []\">Check it out — engage now to earn more rewards!</p>",
                "subtitle" => "New Post 🚀",
                "is_active" => 1,
                "is_delete" => 0,
                "created_at" => "2026-03-12 17:07:10",
                "updated_at" => "2026-03-12 17:07:10",
                "deleted_at" => null
            ],
            [
                "id" => 11,
                "title" => "Forgot Password Super Admin",
                "description" => "<p style=\"font-family: Arial, Helvetica, sans-serif; font-size: small;\">Hi #name#<img data-emoji=\"👋\" class=\"an1\" alt=\"👋\" aria-label=\"👋\" draggable=\"false\" src=\"https://fonts.gstatic.com/s/e/notoemoji/17.0/1f44b/72.png\" loading=\"lazy\" style=\"height: 1.2em; width: 1.2em;\"></p><p style=\"font-family: Arial, Helvetica, sans-serif; font-size: small;\">You requested a password reset. Click below to set a new one:</p><p style=\"font-family: Arial, Helvetica, sans-serif; font-size: small;\"><img data-emoji=\"🔒\" class=\"an1\" alt=\"🔒\" aria-label=\"🔒\" draggable=\"false\" src=\"https://fonts.gstatic.com/s/e/notoemoji/17.0/1f512/72.png\" loading=\"lazy\" style=\"height: 1.2em; width: 1.2em;\"> Link expires in 60 minutes.</p><p style=\"color: rgb(34, 34, 34); font-family: Arial, Helvetica, sans-serif; font-size: small;\"><a href=\"#url#\" target=\"_blank\" data-saferedirecturl=\"https://www.google.com/url?q=https://root.engagereward.com/verify-token/XSyM9171203&source=gmail&ust=1773402809990000&usg=AOvVaw2GnZwvHrRIyzMBcjmeo4Tr\" style=\"color: rgb(17, 85, 204); text-align: center;\">Reset My Password</a></p><p style=\"font-family: Arial, Helvetica, sans-serif; font-size: small;\">Didn’t request this? Just ignore this email or contact us at <a href=\"mailto:#support-mail#\" target=\"_blank\">#support-mail#</a>.</p><p style=\"font-family: Arial, Helvetica, sans-serif; font-size: small;\">Cheers,<br>Team #app-name# <img data-emoji=\"💙\" class=\"an1\" alt=\"💙\" aria-label=\"💙\" draggable=\"false\" src=\"https://fonts.gstatic.com/s/e/notoemoji/17.0/1f499/72.png\" loading=\"lazy\" style=\"height: 1.2em; width: 1.2em;\"></p>",
                "subtitle" => "Reset Your Engage Reward Password",
                "is_active" => 1,
                "is_delete" => 0,
                "created_at" => "2026-03-12 17:27:06",
                "updated_at" => "2026-03-12 17:41:16",
                "deleted_at" => null
            ],
            [
                "id" => 12,
                "title" => "Forgot Password Admin",
                "description" => "<p style=\"font-family: Arial, Helvetica, sans-serif; font-size: small;\">Hi #name#&nbsp;<img data-emoji=\"👋\" class=\"an1\" alt=\"👋\" aria-label=\"👋\" draggable=\"false\" src=\"https://fonts.gstatic.com/s/e/notoemoji/17.0/1f44b/72.png\" loading=\"lazy\" style=\"height: 1.2em; width: 1.2em;\"></p><p style=\"font-family: Arial, Helvetica, sans-serif; font-size: small;\">You requested a password reset. Click below to set a new one:</p><p style=\"font-family: Arial, Helvetica, sans-serif; font-size: small;\"><img data-emoji=\"🔒\" class=\"an1\" alt=\"🔒\" aria-label=\"🔒\" draggable=\"false\" src=\"https://fonts.gstatic.com/s/e/notoemoji/17.0/1f512/72.png\" loading=\"lazy\" style=\"height: 1.2em; width: 1.2em;\">&nbsp;Link expires in 60 minutes.</p><p style=\"color: rgb(34, 34, 34); font-family: Arial, Helvetica, sans-serif; font-size: small;\"><a href=\"#url#\" target=\"_blank\" data-saferedirecturl=\"https://www.google.com/url?q=https://dashboard.engagereward.com/engage/verify-token/sVGaI171203&amp;source=gmail&amp;ust=1773403827569000&amp;usg=AOvVaw0w4s3BrQemo8yi3l98OMWS\" style=\"color: rgb(17, 85, 204); text-align: center;\">Reset My Password</a></p><p style=\"font-family: Arial, Helvetica, sans-serif; font-size: small;\">Didn’t request this? Just ignore this email or contact us at&nbsp;<a href=\"mailto:#support-mail#\" target=\"_blank\">#support-mail#</a></p><p style=\"font-family: Arial, Helvetica, sans-serif; font-size: small;\">Cheers,<br>Team #app-name#&nbsp;<img data-emoji=\"💙\" class=\"an1\" alt=\"💙\" aria-label=\"💙\" draggable=\"false\" src=\"https://fonts.gstatic.com/s/e/notoemoji/17.0/1f499/72.png\" loading=\"lazy\" style=\"height: 1.2em; width: 1.2em;\"></p>",
                "subtitle" => "Reset Your Engage Reward Password",
                "is_active" => 1,
                "is_delete" => 0,
                "created_at" => "2026-03-12 17:44:22",
                "updated_at" => "2026-03-12 17:44:22",
                "deleted_at" => null
            ]
        ];

        foreach ($templates as $template) {
            DB::table('templates')->updateOrInsert(['id' => $template['id']], $template);
        }
    }
}
