# CADViewer Integration for NextCloud

To enable viewing of DWG, DXF, DWF and DGN CAD files using ***[CADViewer](https://www.cadviewer.com)***, please proceed as follows: 

The CADViewer integration with NextCloud consists of the CADViewer front-end integrated with the Nextcloud front-end inteface using VueJS with some back-end components. 

CADViewer internally use php scripts to control the CADViewer CAD Converter back-end AutoXchange (ax2023_L64_xx_yy_zz), which is located in the /nextcloud/apps/cadviewer/ tree. The display process will fetch drawings from the Nextcloud file repository, use AutoXchange to convert them to SVG with CADViewer specific SVG extensions in a temp folder, and then load them into the CADViewer front-end for display and manipulation. 

Note that AutoXchange (for now) is an x86 architecture and thus will not run on ARM architecture. 

There are various ways CADViewer can be installed with NextCloud.

### A: Directly copy this application tree to the NextCloud installation
Follow instructions in ***1.*** and then ensure permission and ownership ***2.*** (see below) as well any rewrite rules in ***4.*** (see below) . 

### B: Install CADViewer via the NextCloud Store on standalone server installation of NextCloud.
Ensure permission and ownership ***2.*** (see below) as well any rewrite rules in ***4.*** (see below). 

### C: Nextclud AIO installation
Follow the instructions in ***5. Integration in NextCloud AIO Docker Setup*** below. 


### D: Install CADViewer via the NextCloud Store, where NextCloud is installed using Snap.

Modify the .htaccess file, which typically is in a structure like: /var/snap/nextcloud/38457/nextcloud/config , likely 38457/nnnnn can vary depending on linux build and Nextcloud version, below is Ubuntu 22.04 LTS / Nextcloud 27.1.3. Add the following rewrite rule (also see Troubleshooting ***4.***): 
```
<IfModule mod_rewrite_c>
	RewriteCond %{REQUEST_FILENAME} !/extra-apps/cadviewer/converter/php/*\.*
	RewriteRule . index.php [PT,E=PATH_INFO:$1]
<IfModule>
```
Navigate to the location of the CADViewer CAD Converter AutoXchange, and provide it with chmod 750 permissions (paths below as for Ubuntu 22.04 LTS / Nextcloud 27.1.3) :

```
sudo su
cd /var/snap/nextcloud/38457/nextcloud/extra-apps/cadviewer/converver/converters/ax2024/linux
chmod 750 ax2023_L64_xx_yy_zz
```
***Note 1:*** Likely, 38457/nnnnn can vary depending on linux build and Nextcloud version.

***Note 2:*** Each time an auto-update of CADViewer is done, the permissions must be reset as per above.

Alternatively, follow the permission instructions in ***2.*** 



## General to-do list items:


### 1. Copy CADViewer to /apps/cadviewer (or /extra-apps/cadviewer or /custom_apps/cadviewer)

Copy the content of this cadviewer install folder and put it in the /apps/ or /extra-apps/ or /custom_apps/ folder of your nextcloud installation under (/apps)/cadviewer/.

### 2. Permissions

In a typical NextCloud installation, the installation is done with owner www-data:www-data and permissions over the files and folders in the app as chmod 750 forfiles and chmod 640 for directories. 
In this case you will not need to to do anything change permission of files, the CADViewer back-end will function normally under these settings.

The standard manual configuration steps from Nextcloud docs to adjust file ownership and permission, can be replicated for the /apps/cadviewer folder, so from the /apps/ (or /custom_apps/ or /extra-apps/)folder run:

```
chown -R www-data:www-data cadviewer
find cadviewer/ -type d -exec chmod 750 {} \;
find cadviewer/ -type f -exec chmod 640 {} \;
```

Then set permission for the CADViewer CAD Converter executable ax2025_L64_xx_yy_zz (legacy ax2023_L64_xx_yy_zz):

```
find cadviewer/ -type f -name "ax2025_L64*" -exec chmod 750 {} \;
find cadviewer/ -type f -name "ax2023_L64*" -exec chmod 750 {} \;
```


Note that if you are installing under ***Snap*** the owner can be different from www-data, and the chown command must be corrected correspondingly. 

If this does not get CADViewer working in your installation, please do one of the following 2A.) or 2B.) below.  Also refer to 4. troubleshooting, section 5.) below.  


#### 2A. Excecute permission script

Navigate to the ***/apps/cadviewer/scripts/*** */ folder and execute the ***permission.sh*** script.     - (alternative /custom_apps/ , /extra_apps/)

This script will do the recommended permission settings (chmod 750) for the relevant CADViewer folders and files. 

If preferred, the user can as an alternative set these permissions manually using the instructions below:


#### 2B. Manually set permissions  - alternative to permission script 2A)

In the NextCloud /apps/cadviewer/ folder-structure, set the recommended permissions (chmod 750) for the following files:

**Execution:**
```
/apps/cadviewer/converter/converters/ax2024/linux/ax2025_L64_xx_yy_zz   - (alternative /custom_apps/ , /extra_apps/)
```
**Scripts folder and files:**
```
/apps/cadviewer/converter/php/call-Api_Conversion_log.txt  - (alternative /custom_apps/ , /extra_apps/)
/apps/cadviewer/converter/php/call-Api_Conversion.php
/apps/cadviewer/converter/php/save-file.php
```

In the NextCloud /apps/cadviewer folder-structure, set the recommended permissions (chmod 640) for the following folders:

**Conversion folders:**
```
/apps/cadviewer/converter/converters/ax2024/linux/     - (alternative /custom_apps/ , /extra_apps/)
/apps/cadviewer/converter/converters/files/
/apps/cadviewer/converter/converters/files/merged/
/apps/cadviewer/converter/converters/files/print/
/apps/cadviewer/converter/converters/files/pdf/
```
**Redlines folders:**
```
/apps/cadviewer/converter/content/redlines/    - (alternative /custom_apps/ , /extra_apps/)
/apps/cadviewer/converter/content/redlines/v7/
```



### 3. Activate CADViewer

1.  Go to the applications menu of NextCloud and accept to use CADViewer as an untested application. 

2.  Activate the NextCloud application.

3.  ***Success!*** You can now visualize your AutoCAD DWG/DXF/DWF and MicroStation DGN files with a simple click in NextCloud!



### 4. Troubleshooting

#### Current versions - version 10.4.1 onwards

CADviewer has implement a reverse proxy why there is no need to change the .htaccess.

1. If the server trace gives a Warning: fopen(call-Api_Conversion_log.txt): Failed to open stream: Permission denied , then likely the current permission settings are insufficient. This case can be seen when added nginx as reverse proxy and SSL certificates on a docker container. In that case the owner or permissions may have changed, repeat the instructions under ***2. Permissions*** above.


#### Legacy versions  - below version 10.3.3


In some cases the automated update of the ***.htaccess*** file is not done. This means that the CADViewer does not connect to the back-end scripts for CAD file conversion. The user experience is that the canvas is white and the "loading.." modal will keep appearing on the screen when attempting to load a file.

1. Go to the install folder of **NextCloud**, this is typically ***/var/www/nextcloud/*** (or where your installation is done), open **.htaccess** in a text editor. See ***C:*** above for installation using Snap.

2. Locate the rewrite rule in place:  ***RewriteRule . index.php [PT,E=PATH_INFO:$1]***

3. Add the rewrite condition ***before*** the rewrite rule: ***RewriteCond %{REQUEST_FILENAME} !/apps/cadviewer/converter/php/\*\\.\****

4. ***NOTE:***  On some installations the Nextcloud ***apps*** installation folder name can change, they can be ***extra-apps** or ***custom-apps***. In that case change the rewrite condition accordingly, for example ***RewriteCond %{REQUEST_FILENAME} !/extra-apps/cadviewer/converter/php/\*\\.\**** .

5. If needed, add both the RewriteRule and RewriteCond. 

6. If the server trace gives a Warning: fopen(call-Api_Conversion_log.txt): Failed to open stream: Permission denied , then likely the current permission settings are insufficient. This case can be seen when added nginx as reverse proxy and SSL certificates on a docker container. In that case the owner may have changed, therefore try inside /cadviewer/converter/php/ to give call-Api_Conversion.php full chmod 750 permission and check if call-Api_Conversion.txt has write permissions for the owner (if you are comfortable, you can give chmod 777 on that also.). If this moves further in the process, you must also then give /converter/converters/files folder, full write permission for all owners, and also give /converter/converters/ax2024/linux/ax2023_L64_xx_yy_zz full chmod 777 permissions to perform the CAD conversions for all owners. You can also gradually change the permission process, 750, 755, 775, 777 to keep control. Always check files against other apps to see if the ownership is correct, otherwise do a chown to change ownership correspondingly for cadviewer.   


### 5. Integration in NextCloud AIO Docker Setup

#### Current versions - version 10.4.1 onwards

The Nextcloud docker AIO is based on an Alpine Linux installation.  This barebone Linux is unable to run c++ applications, therfore in order to run the CADViewer AutoXchange CAD converter, navigate into the Alpine docker container and execute the following: 

```
apk add gcompat
apk add build-base
apk add --no-cache fontconfig
apk add --update binutils
apk add gdb
```

This will provide the libraries needed to make CADViewer run. ***IMPORTANT:*** After this step perform the instructions under ***2. Permissions*** above. 

To check that the converter is operational, run: 

```
cd (location/of/nextcloud)/apps/cadviewer/converter/converters/ax2024/linux/     - (alternative /custom_apps/ , /extra_apps/)
./ax2025_L64_xx_yy_zz -?
```
This will provide a listing that the utility is alive and ready to connect with the front-end. ***Note:*** If glib is not installed properly, there will be a number of linking errors and/or failed execution.



#### Legacy versions  - below version 10.3.3

You need to connect interactively to the nextcloud docker container with the command docker exec -it id_of_container (you get the id with docker ps) and once inside you need to modify the .htaccess file with vim (you install it with these 2 commands: apt-get update, apt-get install vim) and the content to put in your .htaccess is as follows:

```
<IfModule mod_headers.c>
  <IfModule mod_setenvif.c>
    <IfModule mod_fcgid.c>
       SetEnvIfNoCase ^Authorization$ "(.+)" XAUTHORIZATION=$1
       RequestHeader set XAuthorization %{XAUTHORIZATION}e env=XAUTHORIZATION
    </IfModule>
    <IfModule mod_proxy_fcgi.c>
       SetEnvIfNoCase Authorization "(.+)" HTTP_AUTHORIZATION=$1
    </IfModule>
    <IfModule mod_lsapi.c>
      SetEnvIfNoCase ^Authorization$ "(.+)" XAUTHORIZATION=$1
      RequestHeader set XAuthorization %{XAUTHORIZATION}e env=XAUTHORIZATION
    </IfModule>
  </IfModule>

  <IfModule mod_env.c>
    # Add security and privacy related headers

    # Avoid doubled headers by unsetting headers in "onsuccess" table,
    # then add headers to "always" table: https://github.com/nextcloud/server/pull/19002
    Header onsuccess unset Referrer-Policy
    Header always set Referrer-Policy "no-referrer"

    Header onsuccess unset X-Content-Type-Options
    Header always set X-Content-Type-Options "nosniff"

    Header onsuccess unset X-Frame-Options
    Header always set X-Frame-Options "SAMEORIGIN"

    Header onsuccess unset X-Permitted-Cross-Domain-Policies
    Header always set X-Permitted-Cross-Domain-Policies "none"

    Header onsuccess unset X-Robots-Tag
    Header always set X-Robots-Tag "none"

    Header onsuccess unset X-XSS-Protection
    Header always set X-XSS-Protection "1; mode=block"

    SetEnv modHeadersAvailable true
  </IfModule>

  # Add cache control for static resources
  <FilesMatch "\.(css|js|svg|gif|png|jpg|ico|wasm|tflite)$">
    Header set Cache-Control "max-age=15778463"
  </FilesMatch>

  <FilesMatch "\.(css|js|svg|gif|png|jpg|ico|wasm|tflite)(\?v=.*)?$">
    Header set Cache-Control "max-age=15778463, immutable"
  </FilesMatch>

  # Let browsers cache WOFF files for a week
  <FilesMatch "\.woff2?$">
    Header set Cache-Control "max-age=604800"
  </FilesMatch>
</IfModule>

# PHP 7.x
<IfModule mod_php7.c>
  php_value mbstring.func_overload 0
  php_value default_charset 'UTF-8'
  php_value output_buffering 0
  <IfModule mod_env.c>
    SetEnv htaccessWorking true
  </IfModule>
</IfModule>

# PHP 8+
<IfModule mod_php.c>
  php_value mbstring.func_overload 0
  php_value default_charset 'UTF-8'
  php_value output_buffering 0
  <IfModule mod_env.c>
    SetEnv htaccessWorking true
  </IfModule>
</IfModule>

<IfModule mod_mime.c>
  AddType image/svg+xml svg svgz
  AddType application/wasm wasm
  AddEncoding gzip svgz
</IfModule>

<IfModule mod_dir.c>
  DirectoryIndex index.php index.html
</IfModule>

<IfModule pagespeed_module>
  ModPagespeed Off
</IfModule>

<IfModule mod_rewrite.c>
  RewriteEngine on
  RewriteCond %{HTTP_USER_AGENT} DavClnt
  RewriteRule ^$ /remote.php/webdav/ [L,R=302]
  RewriteRule .* - [env=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
  RewriteRule ^\.well-known/carddav /remote.php/dav/ [R=301,L]
  RewriteRule ^\.well-known/caldav /remote.php/dav/ [R=301,L]
  RewriteRule ^remote/(.*) remote.php [QSA,L]
  RewriteRule ^(?:build|tests|config|lib|3rdparty|templates)/.* - [R=404,L]
  RewriteRule ^\.well-known/(?!acme-challenge|pki-validation) /index.php [QSA,L]
  RewriteRule ^(?:\.(?!well-known)|autotest|occ|issue|indie|db_|console).* - [R=404,L]
</IfModule>

AddDefaultCharset utf-8
Options -Indexes
#### DO NOT CHANGE ANYTHING ABOVE THIS LINE ####

ErrorDocument 403 //
ErrorDocument 404 //
<IfModule mod_rewrite.c>
  Options -MultiViews
  RewriteRule ^core/js/oc.js$ index.php [PT,E=PATH_INFO:$1]
  RewriteRule ^core/preview.png$ index.php [PT,E=PATH_INFO:$1]
  RewriteCond %{REQUEST_FILENAME} !\.(css|js|svg|gif|png|html|ttf|woff2?|ico|jpg|jpeg|map|webm|mp4|mp3|ogg|wav|wasm|tflite)$
  RewriteCond %{REQUEST_FILENAME} !/core/ajax/update\.php
  RewriteCond %{REQUEST_FILENAME} !/core/img/(favicon\.ico|manifest\.json)$
  RewriteCond %{REQUEST_FILENAME} !/(cron|public|remote|status)\.php
  RewriteCond %{REQUEST_FILENAME} !/ocs/v(1|2)\.php
  RewriteCond %{REQUEST_FILENAME} !/robots\.txt
  RewriteCond %{REQUEST_FILENAME} !/(ocm-provider|ocs-provider|updater)/
  RewriteCond %{REQUEST_URI} !^/\.well-known/(acme-challenge|pki-validation)/.*
  RewriteCond %{REQUEST_FILENAME} !/richdocumentscode(_arm64)?/proxy.php$
  RewriteCond %{REQUEST_FILENAME} !/apps/cadviewer/converter/php/*\.*
  RewriteRule . index.php [PT,E=PATH_INFO:$1]
  RewriteBase /
  <IfModule mod_env.c>
    SetEnv front_controller_active true
    <IfModule mod_dir.c>
      DirectorySlash off
    </IfModule>
  </IfModule>
</IfModule>

```

and then you just have to follow the instructions in the doc to set permissions on the right file and folder as per **2A:** above.



### Guides
1. [Administrator Interface Guide](https://cadviewer.com/cadviewerusermanual/referenceapplications/nextcloud/admin/).
2. [User Interface Guide](https://cadviewer.com/cadviewerusermanual/referenceapplications/nextcloud/userinterface/).          


### CADViewer Features

CADViewer implements CAD viewing, markup and collaboration on the NextCloud platform for AutoCAD, MicroStation, PDF and advanced raster graphics. Following CADViewer features are available in NextCloud:

- **AutoCAD**: Support for DWG, DXF and DWF files.
- **MicroStation**: Support for DGN files.
- **PDF**: Support for Vector Graphics PDF files. (use ... menu)
- **TIFF**: Support for TIFF format. (use ... menu)
- **PNG, JPG, GIF**: Bitmap support. (use ... menu)
- **Annotation**: Full redlining interface of drawings where each user has individually associated redlines.
- **PDF Collaboration**: Redlines/Annotation on drawings can be shared as PDF to local *CADViewer-Markup* folder, where the user can then share internally/externally.
- **Download**: Direct download of SVG or PDF image with/without redlines/annotations.
- **Printing**: Printing of drawings to printer driver or as PDF.
- **Measurement**: Global scale matrix preserved in drawing for measurement and calibration methods.
- **Zoom**: Advanced zoom and pan controls.
- **Layers**: Retained layer structure for layer management.
- **Search**: Integrated text search method.
- **Compare**: Advanced compare of drawings.

### Instructions on Windows

***[Optional]*** If you are on Windows you will have to modify the file ***apps/cadviewer/converter/php/CADViewer_config.php*** to adapt the configuration to Windows (change executeable name and folders). You will also have to install the Windows back-end CAD converters. Pull the /converters/ax2024/windows/ tree from [cadviewer-script-library](https://github.com/CADViewer/cadviewer-script-library) and replace into the /apps/cadviewer/converter/converters/ tree. In ***apps/cadviewer/converter/php/CADViewer_config.php***, update ***$platform*** and ***$ax2024_executable***.





