# Quickstart: Validating the Privacy Policy Page

## Validation scenarios

| # | Action | Expected Result |
|---|---|---|
| 1 | Load Privacy Policy | States what data is collected (analytics + future candidate data) and why |
| 2 | Load Privacy Policy | States the 24-month retention period from last activity, matching constitution Principle VI exactly |
| 3 | Load Privacy Policy | States how to request access/correction/deletion, with a working contact route |
| 4 | Load Privacy Policy | Describes cookies/analytics and how to decline |
| 5 | Load Privacy Policy | Shows a "last updated" date |
| 6 | From any page, click the footer or cookie-notice Privacy Policy link | Lands on this exact page (not WordPress's own auto-created draft privacy page — see Module 1's `get_privacy_policy_url()` fix) |
| 7 | Load on a 375px-wide viewport | Content stacks without horizontal scroll |

## Done when

All 7 scenarios pass, and the stated retention period matches constitution Principle VI
word-for-word (24 months from last activity).
