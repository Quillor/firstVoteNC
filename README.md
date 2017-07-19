# Steps:

Before doing this steps make sure you have a COMPOSER installed.

## WPENGINE GIT
[GIT LINK](https://wpengine.com/git/)

1. Create a ssh key if you don`t have. This key will be link to wpengine and your github account
  - https://help.github.com/articles/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent/ - create key
  - If your having an issue about the this command `eval $(ssh-agent -s)` then use the bash command or type
```shell
  $ bash 
  $ eval $(ssh-agent -s)
```
2. Add the key to your github account
  - https://help.github.com/articles/adding-a-new-ssh-key-to-your-github-account/
  - Make sure you remember the titled you use here, you will be using it in wpengine portal to identify your keys.
3. Add it to WpEngine portal
  - login to wpengine portal
  - https://wpengine.com/support/set-git-push-user-portal/
  - under the developer name, use the title so you can identify it easily.
4. To check if the connection is working.
  - type this: `$ ssh git@git.wpengine.com info`
  - If you see something like this:
```shell
The authenticity of host `git.wpengine.com` can`t be established.
RSA key fingerprint is 19:17:ee:d2:1d:8d:c9:3e:dc:3e:0d:21:a7:c6:52:fc.
Are you sure you want to continue connecting (yes/no)? yes
Warning: Permanently added 'git.wpengine.com,' (RSA) to the list of known hosts.
```
  - Then all are good, just type YES/yes.
  - In this section, in wpengine this is optional but since we are using the latest files in staging, then we need to download it. 
  - So please login and located to your Install or in our scenario the Ednc -> FirstVote -> Backup points -> Staging and zip the lastest Full backup and send it to your email to download.
  
5. Unzip the files in your localhost, mine is under wamp/www/firstvote/..wp files
6. REMOVE THE MU-PLUGINS DIRECTORY under /wp-content/
7. SETTING UP THE .GITIGNORE…
  - download this file  https://wpengine.com/wp-content/uploads/2013/10/recommended-gitignore-no-wp.txt or copy the whole content and paste it to your editor and save it as .gitignore in your root directory, so `wamp/www/firstvote/HERE`

# WARNING before doing this COMMIT/PUSH do this only if your branch is not outdated   
8. COMMIT
```shell
    $ cd wamp/www/firstvote/
	$ git init .
	$ git add . --all
	$ git commit -m "initial commit..."
```	
  - This is how you commit before pushing the changes , so that there is data to be push to WP Engine.
			
9. DEPLOY TO Staging 
```shell
	$ cd wamp/www/firstvote/
	$ git remote add staging git@git.wpengine.com:staging/firstvotenc.git
```
  - so where do i get this "git@git.wpengine.com:staging/firstvotenc.git", you can view that under the git push section of the site. https://my.wpengine.com/installs/firstvotenc/git_push and that sample above is for firstvote, but each site has it's own value/data.
10. To confirm the remote was added, you can execute: `$ git remote -v`
  - And you should have something like this results
```shell
staging  git@git.wpengine.com:staging/my_wp_install_name.git (fetch)
staging  git@git.wpengine.com:staging/my_wp_install_name.git (push)
```
11. TO DEPLOY
	`$ git push staging master`

12. DEPLOY to production is the same conception of the staging, you only need to replace those staging word.

13. TO PULL
	-`$ git pull staging master` - staging
	-`$ git pull production master` - production
	
	
## HOW TO SETUP/RUN THE WP FILES IN YOUR LOCALHOST
1. WP FILES
  - copy the content of your wp-config-sample to wp-config
  - Open your phpmyadmin and create your DB AND put your DB Details in wp-config file
  - Then Enable MultiSite 
```shell 
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', false); // true or false depending if the paths for each multisite is pointed by a subdomain.
define('DOMAIN_CURRENT_SITE', 'localhost'); // localhost is the DOMAIN of the new location, so even if the ABS_PATH is localhost/whatever, you should have localhost here.
define('PATH_CURRENT_SITE', '/your-wp-install/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);
```
  - Save your wp-config file and open your htaccess and copy this
```shell  
# MultiSite
RewriteEngine On
RewriteBase /your-wp-install/
RewriteRule ^index\.php$ - [L]

# add a trailing slash to /wp-admin
RewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin$ $1wp-admin/ [R=301,L]

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) $2 [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(.*\.php)$ $2 [L]
RewriteRule . index.php [L]
```
  - Save your htaccess and you're done with wp files  

2. DB
  - Your DB/SQL dump is located at wp-content
  - Open your phpmyadmin and create a new DB
  - IMPORTANT BEFORE IMPORTING, go to your my.ini or MYSQL.ini and find this `[mysqld]` add this below
```shell
performance_schema=ON
show_compatibility_56 = ON
```
  - AND Go to your APACHE and open the httpd.conf and find this text `AllowOverride` and change `None to All`
  - THEN Restart your SERVER
  - Import your DB now.

#TIME to EDIT/UPDATE the details
  - in wp_options change fields "site_url" and "home" to `http://localhost/your-wp-install (no trailing slash)`
  - here's the tricky part. In wp_blogs change all `domain` value to `localhost`. Well if you have 100+ data, then you need to query and update it via this command
```shell	
UPDATE wp_blogs SET domain = localhost;
```
  - "path" for every entry for each subsite to /your-wp-install/old-data/ (must have trailing slash)
```shell	
UPDATE wp_blogs SET path = CONCAT('/your-wp-install',path); 
```
  - concat means to combine the static data of your localhost wp url install and old data value. Sample result is `/firstvote/nc-209/`
  - In table wp_site: change "domain" to "localhost", and "path" to /your-wp-install/ (must have trailing slash)
  - In table wp_sitemeta: change "siteurl" to full path ie `http://localhost/your-wp-install/` (must have trailing slash)
  - In this scenario should atleast have access to your page, if you still get redirection loop or white screen of death, double check the database changes. Now login to wp-login
  - Install this plugin https://wordpress.org/plugins/search-and-replace/ OR https://wordpress.org/plugins/better-search-replace/
  - This plugin will search the any text and replace it with a new text, in our case we need to replace the old url to the new url so that the subsite will show.
  - Go to Tools and click the search/replace link
```shell
Search for: http://oldsite.com (http://firstvotenc.staging.wpengine.com)
Replace with: http://localhost/your-wp-install
```
  - This will take time because there are many tables.
  - After it's done replacing, go to main site wp-admin and go to permalinks and click only the save button and check now the sites.
	
	
  
## GITHUB REPO

1. https://github.com/Quillor/firstVoteNC
  - Clone/Download and put it in your localhost directory, mine is c
2. Since the repo is clean, you need to copy all the files except for the wp-config of firstvote wp files in `wamp/www/firstvote/`
3. COMMIT/PUSH
  - All we need to do is just copy and paste the files from staging repo of wpengine which is located in your other folder like mine in `wamp/www/firstvote/`
  - Then add all files, commit and push to your github repo.
 
