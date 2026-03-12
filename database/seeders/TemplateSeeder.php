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
                "description" => "<p style=\"line-height: 1.5; margin-top: 1rem; margin-right: 0px; margin-left: 0px; color: rgb(51, 51, 51); font-size: 16px;\">Hi #name#,</p><p style=\"line-height: 1.5; margin-top: 1rem; margin-right: 0px; margin-left: 0px; color: rgb(51, 51, 51); font-size: 16px;\">You’ve been exclusively invited to join #app-name# — the platform that turns your social engagement into real rewards.</p><p style=\"line-height: 1.5; margin-top: 1rem; margin-right: 0px; margin-left: 0px; color: rgb(51, 51, 51); font-size: 16px;\">🔑 Your invitation code : #invitation-code#</p><p style=\"line-height: 1.5; margin-top: 1rem; margin-right: 0px; margin-left: 0px; color: rgb(51, 51, 51); font-size: 16px;\">📲 Download the <b style=\"line-height: 1.5;\">#app-name#</b> app and enter your code to get started:</p><p style=\"line-height: 1.5; margin-top: 1rem; margin-right: 0px; margin-left: 0px; color: rgb(51, 51, 51); font-size: 16px;\"></p><p style=\"line-height: 1.5; margin-top: 1rem; margin-right: 0px; margin-left: 0px; color: rgb(51, 51, 51); font-size: 16px;\">Start engaging. Start earning.<br><a href=\"https://dashboard.engagereward.com/engage/img/appstore.png\" target=\"_blank\">lo</a><br></p><p style=\"line-height: 1.5; margin-top: 1rem; margin-right: 0px; margin-left: 0px; color: rgb(51, 51, 51); font-size: 16px;\"><img style=\"width: 200.509px; height: 71.4028px;\" src=\"data:image/png;base64,UklGRlIcAABXRUJQVlA4TEYcAAAv4sEqEBWH4rZtHGn/sZPr9RkRE8DRXTZZ39Vsca76AFichlqZO2jFpO6SGjomjDQSWEDFvIcy1oRFVWS4vWwcEHug/HJvzLY9m9psG8VJXHHBvffee+/dKS7cpPfee++9V7f0nmAwzDAE3LvTE/feu1Pce8Hlx7Wfx3HMzDXXxXXXsYWMTbILCw8RQyoyEwunebBSxsi47LmJxg0ZYRF5XCiKIXdDZmz5ctOF4gEZdyvI7R4rBlJwGwXFmxEYnIaHKCjYAQthyxokj53iEpPmIpADxRYAhMmfr++tLe4Mvug7wfoGVE5ApDlpCdfsmriAe4MkyR2gBgKAILoB2EEXoMMM7jQajcU0unt6zV7d/V8Obdum9th446+cVLZt27ZtVKxSOqls27Zt23be135zAuj6/21qc8a2bdvmg7Fj27adsW3rtf2+d5dnztlrnR3sNhWrHZcpUxmlVrA+cw2pVhVnh/V8UqqbMqjmkzq3kNxAqlyCjX4kQQBANpJt27Zt27Zt2y8kW9te27Z597Jt+27kAABANlLONifbtm3btjXZGG3bto23bRtxiqT/ExD4H0hdjPjJqOHNiAzsTc11b4yHt8J1un8zLx56syUdkyah/nSk+aUpqZqWuan5za7ZVPzxYfkk1J6HRD8yMbV9uMiu3PE59zaav4NEPzAZud5sZ8XX47VwndZ/CyyY03J2Cir62oT0TMlvJsfGQzAcusLNkACu1FqdY2jcHy9DExtOyZ/0jPch0Xe8mMO2b8Zj0RcSQlyx7p5Q+3VgZk4BH9ZPROALM5KWnqNs+yR8IQFct53i4XgTmJlTsiUNgbm3cZSZ+bUYFuLaHRhjgZk5FVu8CUxdxcz8UuQWV29HeDqYmdOydloyzGSgJzPzi5AEZH7bwGPAzCngzYTFiDMxDUXHJ8rMP8qXACY43+nM/P/pqG0iDduZ+TXoBOIa7hgvBDN7cWJasvUmowUz81AQV/FAKMbM6Ris58NtZn4CxGXsvg+YOTWXpyRHZxKqMvOb0QlcR9IZxgQzp6GdTlqWMPMjIa7ku4KZvdg0HclqX0lDlJl7gUupZ7wSzKn46EMJtYmpz8zfEJdyYpzOzOnpp5aWscz8ALiW5DZg5rTMV/NhPzMPBRfTgHgDmNNweBYCJS+eMPOVLqYjvsE8Hje8yFdKDeYmLcHFlO8FYPYinJTySsz8aoiLOf5ZYC6Ugao6FV1N8kwwp4IPVf/9BblHRLanoMbjcGcMirYR4MQXHB21azwND0dy9IHgTF2t4vPTY21YroGJcCvcFCp3w0rQXQQi7SDa5JBQGhXR0dFniEi0yTvAQO6PLQpYLonnoFso/SM6Ojr6crVDo6OjH4xMN+5k+CCgPh+GgcK9AN2aIkkBkzsqjQEAO4oITN4FWq3g9NUA5Yx4LlqHwsAAgGXQPpT6AYDdnYlKP2ldH34bffFy//3Hk4+eDnp+qbPHRwFFaygGMm5xLIiaa8EGnmcjEQKGotMB5IpovDCWhw0wF7qDBrzhvNT+jBy7emfr4HwcMBvzhZM9A19cS/hPQNBicqwNAB/GwYMgMURk/dtjQsQBwBOhVP/FUH0mRI7NTzOAaZCfzmwkP70pgBmQnx4AGl9YDAAwCx6PzCIiXeJh2AIAlkaf0MDIcFIS/v8L3XyzBTIvHp85N9bFwwP7dzNMDABxF4SIdfeYEsD70TqUwsTwXwHsLiatLK8DkF+MdoBtASyF40UxGfYDsFMX0JgfIc7JrQ9OifcUQ0133TuQ5O2T7p4HAM1HhCjHXJI5VgKAm2AqAJgXR4hyjhkAYCbUUkgDAPmdkiFPLpTw/dqJrtDNt59pbSAAiM4pmu4ICQiqAqjcIUSzbcwBAN9RyDkngNVR0AlJ+vzujaNteiVkJm2n9YvfGnO/EwAqiC9qrEUeYHV8WbR7xCoAlkWbsDrkFA/wXridj7vsHyph9N8Tl25W+4Zrq38AeB8kQLoDAKwjBrMC+I7VLSGvAIAHwelo5evbm0dazIhshC1Er1+X+kdq6ksARkXAtD0Q1AVM5MgI4CCrcGkX1wDLoH04Gz3cB7x5fMGUyHzSVvunv+zTr4Fr4gOk2CDgfDGaFsCaaGRRXOQsAN5wNga+jXnuRsmbx2aMiTx4dafdpzd2qC0A2EACpL4BYByYuQ8AjAQV9+QAcJ6TMeJN3DP3Gp63nveW0Ql1cv+Ek2el/zG10ysA/MT3ip2kWNV+nAcgm5nhAGATFekBqwKYDyHOxcjXCdmzP38taSt0V53IYsJGl4ffdfNlAE+FyotQWXkBtAQL5Q3sx1kAspjpDwBqKMmFANZzOrLPnvfeBtkCEDl/8rzeR1gv/w1gX5X6UO8cdu22AHArmOkXAJ4FtZB5AEQWdDrsnDmPDyFkC2R+yuHTcr8ZOukTAF4Cc2EWs+L9UBwL9uO3AIqbuRTAY6Amg8MDvAdup8POmfP4EEJkOXa1190vGtkOgDdUrshvvT2AjmARJoYDNhAA1DVzRwBIBg0pBuABEKnjZNg5c27bIBsgIpeOnjR8B2nDfQ2wAFRULwAyIt6utQsAk8HMqwDgOp32kQfI01R6hbNh5844bwdXYGTb4NxBuw8r/KJrQtYB0D+MVAfyil2T6sBesSZCdgUqi44cDqC8hDkddt60zdcwIqvQK/1vffxMD0cDKG/iBwDGh53bHcDWJu4EAJvqua8CMCKucz7s/Enz7eAykMjV7IOmb/5podC2gGcIGPg7gGqayx0H5Mmh1zY+CCCuZ+jJD1YHMB+6hhNiv9oOLkPJv0ds3qv8g9CAJAeAxuW0hgaABZEYmpOTAFwVpVMoF4DyYkBOB1DFGbFfbQeXoETWIJeH33z7WfDcGwCYDUdoFF4BAH4muivVGMDb0DLUol4HANfkMBJRCaTzYRdMGG0Hl8BEZtB3W778GzhpuhAA7FRTJf7wlQGgvNg96R+rAMB7UEalM0wKURZfE1Bv+Ay4JWCQ666yUc1cUMzJneG82Bd2YShR4kGre0mf3/gvkYLl91JYHm9ENxBF3YlEvxPhVp4trhVVA4nwdjgv2cdciSqHQjbCFseevSz9jxkskYjk+MmEeBcyvwP18t8CUaJcKky3gUrLCAsLa2QkNiwsLEKlUFhYWPvwBZErL68yOU48fyq8Bbt3DVEPCQsLSwI1aREWFtbISck+1nJQHDCHQmT57lAP6bsBy6Srg+xLOeI4kuY6Cp29ijzdrjCprSf0zeDYZWhR4kGre0mf3/gvkYLl91JYHm9ENxBF3YlEvxPhVp4trhVVA4nwdjgv2cdciSqHQjbCFseevSz9jxkskYjk+MmEeBcyvwP18t8CUaJcKky3gUrLCAsLa2QkNiwsLEKlUFhYWPvwBZErL68yOU48fyq8Bbt3DVEPCQsLSwI1aREWFtbISck+1nJQHDCHQmT57lAP6bsBy6Srg+xLOeI4kuY6Cp29ijzdrjCprSf0zeDYZ\" data-filename=\"playstore (1).png\"><img style=\"width: 196.25px; height: 69.887px;\" src=\"data:image/png;base64,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\" data-filename=\"playstore (1).png\"><br></p><div style=\"line-height: 1.5; color: rgb(51, 51, 51); font-size: 16px;\">Cheers,</div><p><span style=\"color: rgb(51, 51, 51); font-size: 16px;\"></span></p><div style=\"line-height: 1.5; color: rgb(51, 51, 51); font-size: 16px;\">#app-name# 💙</div><div style=\"line-height: 1.5; color: rgb(51, 51, 51); font-size: 16px;\"><br></div>",
                "subtitle" => "🎉 You're Invited to Join Engage Reward!",
                "is_active" => 1,
                "is_delete" => 0,
                "created_at" => "2026-03-11 22:24:30",
                "updated_at" => "2026-03-12 16:39:26",
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
