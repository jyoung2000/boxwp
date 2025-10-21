# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |

## Security Features

### Encryption

#### AES-256-CTR Encryption
All sensitive credentials are encrypted using OpenSSL AES-256-CTR before storage:
- Box Client ID and Secret
- Enterprise IDs
- Private keys and passphrases
- JWT tokens

#### Encryption Key Storage
- Encryption keys stored in `wp-config.php` only
- Never stored in database
- Never exposed in code or logs
- Never transmitted over network

### Data Protection

#### Input Sanitization
All user inputs sanitized using WordPress functions:
- `sanitize_text_field()` - Text inputs
- `sanitize_textarea_field()` - Textarea inputs
- `sanitize_email()` - Email addresses
- `absint()` - Integer values
- `esc_url_raw()` - URLs

#### Output Escaping
All outputs escaped based on context:
- `esc_html()` - HTML content
- `esc_attr()` - HTML attributes
- `esc_url()` - URLs
- `esc_js()` - JavaScript
- `wp_kses_post()` - Rich content

### Access Control

#### Capability Checks
All admin functions require proper capabilities:
```php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Unauthorized access' );
}
```

#### Nonce Verification
All AJAX requests verified with nonces:
```php
check_ajax_referer( 'bas_search_nonce', 'nonce' );
```

### WordPress Security Standards

#### SQL Injection Prevention
- Uses WordPress prepared statements
- Escapes table/column names
- Sanitizes all inputs

#### XSS Prevention
- Escapes all outputs
- Validates input types
- Uses WordPress sanitization

#### CSRF Protection
- Nonce verification on all forms
- Nonce verification on all AJAX
- Unique nonces per action

## Best Practices

### For Site Administrators

1. **Protect wp-config.php**
   ```bash
   chmod 400 wp-config.php
   ```

2. **Use HTTPS**
   - Always use SSL/TLS
   - Force HTTPS in wp-config.php:
     ```php
     define( 'FORCE_SSL_ADMIN', true );
     ```

3. **Regular Updates**
   - Keep WordPress updated
   - Keep PHP updated
   - Keep plugin updated

4. **Backup Encryption Key**
   - Store in password manager
   - Keep offline backup
   - Never share publicly

5. **Limit Admin Access**
   - Only trusted users
   - Use strong passwords
   - Enable two-factor authentication

6. **Monitor Logs**
   - Check WordPress debug.log
   - Monitor PHP error_log
   - Review access logs

7. **Regular Security Audits**
   - Review user permissions
   - Check for suspicious activity
   - Verify credentials still valid

### For Developers

1. **Never Commit Secrets**
   - Use .gitignore
   - Keep credentials out of code
   - Use environment variables

2. **Validate All Inputs**
   - Check data types
   - Validate ranges
   - Sanitize immediately

3. **Escape All Outputs**
   - Choose correct escaping function
   - Escape late (just before output)
   - Never trust data

4. **Use WordPress APIs**
   - Leverage built-in security
   - Follow coding standards
   - Use WordPress functions

5. **Handle Errors Safely**
   - Never expose sensitive data
   - Log errors securely
   - Show generic messages to users

## Reporting a Vulnerability

### How to Report

If you discover a security vulnerability, please email:

**Email**: security@example.com

**Please include**:
- Description of vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

### What to Expect

1. **Acknowledgment**: Within 48 hours
2. **Assessment**: Within 7 days
3. **Fix Development**: Priority based on severity
4. **Release**: As soon as safely possible
5. **Disclosure**: After fix is released

### Please Do Not

- Post vulnerabilities publicly
- Exploit vulnerabilities
- Test on production sites without permission

## Security Checklist

### Installation Security

- [ ] Generated strong encryption key
- [ ] Added key to wp-config.php
- [ ] Set wp-config.php permissions (400 or 440)
- [ ] Using HTTPS
- [ ] Verified OpenSSL installed
- [ ] WordPress is up to date
- [ ] PHP is version 8.0+

### Configuration Security

- [ ] Box credentials encrypted
- [ ] Test connection successful
- [ ] Only admins can access settings
- [ ] Strong Box app credentials
- [ ] Box app scopes minimal
- [ ] Cache expiration set appropriately

### Operational Security

- [ ] Regular backups configured
- [ ] Error logging enabled
- [ ] Monitoring in place
- [ ] Access logs reviewed
- [ ] Plugin kept updated
- [ ] No debug info in production

## Threat Model

### Threats Considered

1. **Credential Theft**
   - Mitigation: AES-256 encryption
   - Defense: Key in wp-config.php only

2. **SQL Injection**
   - Mitigation: Prepared statements
   - Defense: Input sanitization

3. **XSS Attacks**
   - Mitigation: Output escaping
   - Defense: Input validation

4. **CSRF Attacks**
   - Mitigation: Nonce verification
   - Defense: Capability checks

5. **Man-in-the-Middle**
   - Mitigation: HTTPS required
   - Defense: SSL/TLS verification

6. **Privilege Escalation**
   - Mitigation: Capability checks
   - Defense: WordPress user roles

### Out of Scope

- Physical server access
- WordPress core vulnerabilities
- Box API vulnerabilities
- Network-level attacks
- Social engineering

## Security Updates

### Version History

| Version | Date       | Security Fix                    |
| ------- | ---------- | ------------------------------- |
| 1.0.0   | 2025-01-XX | Initial secure implementation   |

### Update Process

1. Security issue identified
2. Patch developed and tested
3. Version bumped
4. Release notes published
5. Users notified
6. Automatic updates (if enabled)

## Compliance

### GDPR Considerations

- No personal data collected by plugin
- Search queries sent to Box AI (see Box privacy policy)
- Credentials stored encrypted
- Data can be deleted on uninstall

### Data Storage

**What we store**:
- Encrypted Box credentials
- Cache of search results (configurable expiration)
- Plugin settings

**What we don't store**:
- User personal information
- Search history
- User behavior data
- Analytics or tracking data

## Additional Resources

- [WordPress Security Best Practices](https://wordpress.org/support/article/hardening-wordpress/)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Box Security](https://www.box.com/security)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)

## Contact

For security concerns:
- **Email**: security@example.com
- **GitHub**: https://github.com/jyoung2000/boxwp/security

---

**Security is a shared responsibility**. Please help us keep WordPress and Box AI Search secure by following best practices and reporting vulnerabilities responsibly.
