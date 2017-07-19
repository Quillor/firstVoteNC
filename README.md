Steps:

Before doing this steps make sure you have a COMPOSER installed.

## https://wpengine.com/git/
	* Create a ssh key if you don`t have. This key will be link to wpengine and your github account
		* https://help.github.com/articles/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent/ - create key
		* If your having an issue abou the this command `eval $(ssh-agent -s)` 
		  then use the bash command or type `bash` first (https://stackoverflow.com/questions/35709231/cmder-eval-command-is-not-recognized)
	* Add the key to your github account
		* https://help.github.com/articles/adding-a-new-ssh-key-to-your-github-account/
		* Make sure you remember the titled you use here, you will be using it in wpengine portal to identify your keys.
	* Add it to WpEngine portal
		* login to wpengine portal
		* https://wpengine.com/support/set-git-push-user-portal/
		* under the developer name, use the title so you can identify it easily.
	* To check if the connection is working.
		* type this: `$ ssh git@git.wpengine.com info`
		* If you see something like this:
			`The authenticity of host `git.wpengine.com` can`t be established.
			RSA key fingerprint is 19:17:ee:d2:1d:8d:c9:3e:dc:3e:0d:21:a7:c6:52:fc.
			Are you sure you want to continue connecting (yes/no)? yes
			Warning: Permanently added 'git.wpengine.com,' (RSA) to the list of known hosts.`
			* Then all are good, just type YES/yes.
	* In this section, in wpengine this is optional but since we are using the latest files in staging, then we need to download it. 
	  So please login and located to your Install or in our scenario the Ednc -> FirstVote -> Backup points -> Staging 
	  and zip the lastest Full backup and send it to your email to download.
	  
	* Unzip the files in your localhost, mine is under wamp/www/firstvote/..wp files
	* REMOVE THE MU-PLUGINS DIRECTORY under /wp-content/
	* SETTING UP THE .GITIGNORE…
		* download this file  https://wpengine.com/wp-content/uploads/2013/10/recommended-gitignore-no-wp.txt OR
		  Copy the whole content and paste it to your editor and save it as .gitignore in your root directory
		  so wamp/www/firstvote/... here
	
	* COMMIT
	   ` $ cd wamp/www/firstvote/
		$ git init .
		$ git add . --all
		$ git commit -m "initial commit..." `	
		* This is how you commit before pushing the changes , so that there is data to be push to WP Engine.
				
	* DEPLOY TO Staging 
		` $ cd wamp/www/firstvote/
		$ git remote add staging git@git.wpengine.com:staging/firstvotenc.git `		
		* so where do i get this "git@git.wpengine.com:staging/firstvotenc.git", you can view that under the git push section of the site. https://my.wpengine.com/installs/firstvotenc/git_push and that sample above is for firstvote, but each site has it`s own value/data.
	* To confirm the remote was added, you can execute: `$ git remote -v`
		*and you should have something like this results
		`staging  git@git.wpengine.com:staging/my_wp_install_name.git (fetch)
		 staging  git@git.wpengine.com:staging/my_wp_install_name.git (push)`
	* TO DEPLOY
		`$ git push staging master`
	
	* DEPLOY to production is the same conception of the staging, you only need to replace those staging word.
	
	* TO PULL
		`$ git pull staging master` - staging
		`$ git pull production master` - production
		
