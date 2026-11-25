# Security Policy

## Supported versions

Only the latest code on the default branch receives security fixes.

## Reporting a vulnerability

Please **do not** open a public issue for security problems.

Report vulnerabilities privately through GitHub:
**Security → Report a vulnerability** on this repository
(GitHub private vulnerability reporting).

Please include:

- A description of the issue and its impact
- Steps to reproduce (or a proof of concept)
- The affected file(s), route(s) or version

You can expect an initial response within 7 days. Once the issue is confirmed,
a fix will be prepared and released, and you will be credited if you wish.

## Handling secrets

- Never commit `.env` files, API keys, passwords or private keys.
- Use `.env.example` with placeholder values only.
- If a secret is committed by mistake, rotate it immediately. Removing the
  file in a later commit does not remove it from git history.
