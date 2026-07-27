# Quickstart: Validating the Contact Us Page

## Validation scenarios

| # | Action | Expected Result |
|---|---|---|
| 1 | Load Contact Us | Office address is visible |
| 2 | Load Contact Us | Phone number is visible as a working `tel:` link |
| 3 | Load Contact Us | Email address is visible as a working `mailto:` link |
| 4 | Tap the phone number on a mobile device | Native call action triggers |
| 5 | Scroll the page | No functional contact form present (FR-004) |
| 6 | Load on a 375px-wide viewport | Content stacks without horizontal scroll |

## Done when

All 6 scenarios pass.
