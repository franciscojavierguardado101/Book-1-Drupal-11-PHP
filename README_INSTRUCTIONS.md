# Pantheon New Site Setup Instructions
## How to create and set up a new Drupal 11 site on Pantheon (new dashboard workflow)

---

## Prerequisites

### 1. Install / Update Terminus
```bash
brew upgrade pantheon-systems/external/terminus
```
If the Composer-installed version is shadowing Homebrew, remove it:
```bash
composer global remove pantheon-systems/terminus
terminus --version
# Should show 4.3.3 or later
```

### 2. Generate a Machine Token
- Log into Pantheon with your account
- Go to: **Account → Machine Tokens → Create Token**
- Give it a name (e.g. `local-mac`)
- Copy the token — you only see it once

### 3. Authenticate Terminus
```bash
terminus auth:login --email=YOUR_EMAIL@gmail.com --machine-token=YOUR_TOKEN_HERE
terminus auth:whoami
# Should show your email
```

### 4. Add SSH Key to Pantheon
- Go to: **Account → SSH Keys → Add Key**
- Paste the contents of your public key:
```bash
cat ~/.ssh/id_rsa.pub
```
- Paste the full output into the Pantheon SSH Keys field and save

---

## Creating a New Site

### 5. Find Your Org ID
```bash
terminus org:list
# Copy the ID of your workspace
```

### 6. Create the Site via Terminus
```bash
terminus site:create <site-machine-name> "<Site Label>" drupal-11-composer-managed --org=YOUR_ORG_ID
```
Example:
```bash
terminus site:create francisco-guardado-book-1 "Francisco Guardado Book 1" drupal-11-composer-managed --org=a8004126-7235-4769-9eae-4e2d8200233d
```
> Note: The Pantheon dashboard no longer shows a "Create New Site" button on the personal My Dashboard view. Always use Terminus to create sites.

### 7. Get the Git Clone URL
```bash
terminus connection:info <site-name>.dev --fields=git_url
```

---

## Local Development Setup

### 8. Trust the Pantheon SSH Host Key
```bash
ssh-keyscan -p 2222 codeserver.dev.<SITE-UUID>.drush.in >> ~/.ssh/known_hosts
```
Replace `<SITE-UUID>` with the UUID from your Git URL.

### 9. Clone the Repo
```bash
git clone ssh://codeserver.dev.<SITE-UUID>@codeserver.dev.<SITE-UUID>.drush.in:2222/~/repository.git /path/to/your/local/folder
cd /path/to/your/local/folder
```

### 10. Configure DDEV
```bash
ddev config --project-type=drupal --php-version=8.3 --docroot=web --project-name=<site-machine-name>
ddev add-on get kanopi/ddev-pantheon-toolkit
```

### 11. Start DDEV
> Run this yourself in your terminal — it will prompt for your Mac password (sudo required to update /etc/hosts):
```bash
ddev start
```

### 12. Allow the symfony/runtime Composer Plugin
> Required for Drupal 11 Composer installs — only needed once per project:
```bash
ddev composer config --no-plugins allow-plugins.symfony/runtime true
```

### 13. Install Composer Dependencies
```bash
ddev composer install
```

### 14. Install Drupal
```bash
ddev drush site:install --account-name=admin --account-pass=admin --site-name="Your Site Name" -y
```

---

## Push to Pantheon

### 15. Add .ddev to .gitignore
Add this to the bottom of `.gitignore` before committing:
```
# Ignore local DDEV configuration
/.ddev/
```

### 16. Commit Your Changes
```bash
git add composer.json composer.lock web/sites/default/settings.php .gitignore
git commit -m "Initial Drupal 11 setup with Composer dependencies and site install"
```

### 17. Switch Pantheon Dev to Git Mode
> Pantheon starts in SFTP mode — you must switch it before you can push:
```bash
terminus connection:set <site-name>.dev git
```

### 18. Push to Pantheon Dev
Pantheon's Dev environment uses the `master` branch. Push your local `main` to it:
```bash
git push origin main:master
```
> Pantheon will run Integrated Composer automatically. Watch the build log in the dashboard.

---

## Your Site URLs

| Environment | URL |
|-------------|-----|
| Local (DDEV) | `https://<site-machine-name>.ddev.site:33300` |
| Pantheon Dev | `https://dev-<site-machine-name>.pantheonsite.io` |
| Pantheon Test | `https://test-<site-machine-name>.pantheonsite.io` |
| Pantheon Live | `https://live-<site-machine-name>.pantheonsite.io` |

---

## Day-to-Day Workflow

```bash
# Start local dev
ddev start

# Work on code locally, then push to Pantheon Dev
git add .
git commit -m "Your message"
git push origin main:master

# Deploy Dev → Test via Terminus
terminus env:deploy <site-name>.test --sync-content --note="Deploy to test"

# Deploy Test → Live via Terminus
terminus env:deploy <site-name>.live --note="Deploy to live"
```

---

## Useful Terminus Commands

```bash
# Check which account is active
terminus auth:whoami

# Switch accounts
terminus auth:login --email=OTHER_EMAIL@gmail.com

# List all your sites
terminus site:list

# Check site info
terminus site:info <site-name>

# Open the site in browser
terminus dashboard:view <site-name>
```




Note:
Pantheon shifted toward a more enterprise/agency-focused platform. A few reasons:                                                          
                                                                                                                                             
  Business model shift                                                                                                                       
  They moved away from catering to solo developers and small sites. Their money is in agency teams and large organizations, so the UI is now 
  built around "Workspaces" (team environments) rather than personal dashboards.                                                             
                                                                                                                                             
  Integrated Composer push                                                                                                                   
  Drupal 10/11 requires Composer to manage dependencies. Pantheon built Integrated Composer to handle this server-side, but it adds        
  complexity to the git workflow (the auto-commits you saw, the master vs main branch issue, the SFTP/Git mode switching).                   
                                                                                                                                           
  WebOps positioning                                                                                                                         
  They rebranded around "WebOps" — treating websites like software development pipelines. That means more Terminus, more CI/CD, more GitHub
  Actions integration. Less click-and-go dashboard.                                                                                          
                                                                                                                                           
  The tradeoff                                                                                                                               
  The old workflow (SFTP files directly, click "Create Site" in dashboard) was beginner-friendly but didn't scale well for professional    
  teams. The new workflow is more powerful but assumes you know Git, Composer, and CLI tools.                                                
                                                                                                                                           
  Bottom line — Pantheon is now really a platform for developers who work in teams with proper deploy pipelines. For solo portfolio projects 
  like yours, it's overkill, but the free sandbox slots still make it worth using. The README you have documents the real workflow so you  
  never have to figure it out again. 