

General information
---------------------------------------------------------------------

- Type: content management system/framework
- Author: Maxim Rysevets
- Developer: Maxim Rysevets
- Start of development: end of 2016
- Initial release: 2021-06-01
- Written in: PHP
- Operating system: UNIX, Linux, Microsoft Windows
- Is open source project: yes
- License: proprietary software
- Website: http://effcore.com

EFFCORE — the next-generation mini CMS (Content Management System)
and mini CMF (Content Management Framework) developed by between 2016 and 2021.
Uses technologies such as: HTML5, CSS3, IP v6, SVG, SMIL,
UTF8, UTC, WAI-ARIA, NoSQL, Markdown, UML, PSR-0.

Can be used as a tool for building sites by simple users, and also
as a tool for the development of sites, portals and services by
professional web developers.

The name of the system is an acronym and comes from the phrase "effective core".
The system was made from a scratch. Its main principle — complete absence of
third-party code (for excluding legal claims and any other restrictions associated
with borrowing), and complete absence of third-party ideas (for search innovative
solutions).

The impetus for the creation of the system was a massive degradation in the
development of Open Source projects. If we consider this situation,
then we can see that the developers of such projects are people with the
different skill levels and located in different parts of the planet.
Inconsistency in their actions and also different views on the solutions
in the project are only part of the problem. The second significant
drawback — this is an extensive way of developing the code of such
projects as opposed to the intensive, i.e. instead of creating their
own code, they usually take an another library which was written by
someone unknown and it is not clear by where and it is not clear for
what and they try to combine this library with a group of the same
libraries which are not consistent with each other and have redundant
and not fully tested functionality. As the result we get a set of
incomprehensible and inconsistent libraries with redundant and
poorly tested functionality, which is constantly growing in volume.
Unfortunately, many developers are bogged down in such code and
are trying to deny the obvious.

The main focus of the system is on getting maximum performance.
The evaluation criterion is a simple and understandable condition:
the system which was installed on hosting with the cheapest tariff plan
which compliant with the minimum installation requirements (from
~3-5$ per month), should generate the front page in 0.005 seconds
if OPCache is enabled or 0.05 seconds if OPCache is disabled, which
in the first case allows serving up to ~200 clients per second and
in the second case allows serving up to ~20 clients per second
(excluding concurrent file downloads).


Content management
---------------------------------------------------------------------

A set of layouts is available to the user in the system. Each layout has
a certain number of regions. Blocks with text, menus, forms (only in the
"content" region) and others can be placed in each region. Each page can
have an individual layout. Thus, the markup of any page can be unique.


Updates
---------------------------------------------------------------------

Timely system update provides:

-	new functionality;
-	bug fixes;
-	performance improvement;
-	security improvement.

To update the system in manual mode, you need to go to its official website
effcore.com and download the latest distribution kit in the form of an archive.
Next, you should unpack the downloaded archive locally.
Important: among the unpacked files you will find half-empty directories "modules"
and "dynamic" which must be removed before copying to the web server!
They need to be deleted because in some operating systems, when copying directories,
the old directories are completely replaced with new ones, and not merged,
which will lead to the loss of data on the web server.
Next, you should copy the remaining files to the web server so that the new
files from the distributions replace the old files on the web server.
After that, in the administrative interface of the system, visit the section
"Management → Modules → Update → Data" and perform an update for each module
if required.

To update the system through the Git repository, go to the section
"Management → Modules → Update → Files from repository" in the administrative interface
of the system and perform the update in one click with the "update" button.
If the "update" button is not available, but the "restore repository" button is available,
then you must first perform the procedure for restoring the repository.
If the "update" and "restore repository" buttons are unavailable, then the specified
module does not have its own repository and its update via Git is not possible.

The update process via the Git repository can also be performed from the
terminal/console/shell by logging into the web server via SSH connection and
navigating to the "shell" directory and then running the "./update.sh" script.
This update is only possible if there is a ".git" directory in the web root.


Security
---------------------------------------------------------------------

Security is an important factor in the system.

The following attack vectors were reviewed:

- An attacker can try to get access to SQLite data, system keys.
- An attacker can try to get access to files such as ".htaccess", ".nginx", "web.config",
  directories "/.git/", "/dynamic/tmp/.git_restore-system/" and others.
- An attacker can try to find a weak point in the directives in the web server
  settings file (".htaccess", ".nginx", "web.config") if there are many directives and/or
  they are written incorrectly and/or they do not predict all possible situations and/or
  after a software update the rules for formatting directives in such files have changed.
- An attacker can try to access files outside the web root directory by manipulating such
  combinations as "./", "../", "~/", "//" and others.
- An attacker can try to enter data for SQL injection into form fields.
- An attacker can try to spoof the session identifier.
- An attacker can try to spoof the form validation identifier.
- An attacker can try to submit a pre-filled form multiple times (authentication form
  "form_login", new user registration form "form_registration", password recovery
  form "form_recovery") in order to brute-force the email address and/or username
  and/or password or bypassing the CAPTCHA.
- An attacker can try to unblock blocked fields on a form using a browser.
- An attacker can try to send a larger field value than allowed by the
  attributes "maxlength", "max", "step", "min", "max" and others.
- An attacker can try to make GET/POST requests with characters that are not
  allowed by RFC standards.
- An attacker can try to make GET/POST requests in which the dimensions of the
  transmitted arrays or their indices may not correspond to the acceptable ones.
- An attacker can try to substitute invalid arguments in the
  URL request (http://domain/path?QUERY).
- An attacker can try to gain access to a user profile with temporary access
  to his workplace.


Security solutions
---------------------------------------------------------------------

File vector:

- Web server settings files (".htaccess", ".nginx", "web.config") contain directives
  that prohibit user agent access to directories "/dynamic/cache/", "/dynamic/data/",
  "/dynamic/logs/". An attacker will not be able to access SQLite data, system keys.
- Web server configuration files (".htaccess", ".nginx", "web.config") contain a directive
  that prohibits user agent access to any files or directories whose name begins with
  the "." at any nesting level. An attacker will not be able to access such files as ".htaccess",
  ".nginx", directories "/.git/", "/dynamic/tmp/.git_restore-system/" and others.
- Web server configuration files (".htaccess", ".nginx", "web.config") contain a directive
  that prohibits user agent access to the "web.config". An attacker would be unable
  to access the "web.config" file.
- Web server configuration files (".htaccess", ".nginx", "web.config") contain a directive
  that provides a single entry point to the "index.php" file, which guarantees a single and
  consistent approach to protecting any file on the system.
- Additionally, at the PHP level, the system provides restriction of user agent access
  to the web server file system outside the web root directory.
- Additionally, at the PHP level, the system provides filtering in URL requests that
  contain such combinations as "./", "../", "~/", "//".
- Additionally, at the PHP level, the system provides user agent access only to the
  actually existing file (except for the "kind: virtual" type).
  In this case, the PHP script must have the right to read the requested file.
- At the PHP level, the system restricts access to files whose type is
  set as "protected" ("kind: protected").
- At the PHP level, the system can organize additional restrictions on access to any type
  of file (at the request of the developer through the "on_load" event handler).

Vector DB:

- Before executing any SQL query, it is prepared, which excludes
  the possibility of SQL injection.

Session vector:

- The session identifier "session_id" is signed with the "settings/core/keys/session" key
  located on the web server side, which makes it impossible to forge.
- The session identifier can contain the name of the user agent and its IP address (during
  authentication, the user himself determines whether to bind his session to his IP address)
  which makes hijacking the session identifier a meaningless procedure — the attacker's
  request from a different IP address will be ignored.
- The session identifier may have a short-term validity (during authentication, the user
  himself determines whether his session is short-lived or not).
- The session identifier is not cross-domain by default, i.e. not transferred
  to third party domains.

HTTP request vector:

- The form validation identifier "validation_id" is signed with
  the "settings/core/keys/form_validation" key located on the web server side, which makes
  it impossible to forge.
- The validation identifier of the form contains the name of the user agent and its IP address,
  which makes intercepting the identifier a meaningless procedure — an attacker's request
  from a different IP address will be ignored.
  The lifetime of the validation identifier is limited in time.
- The data validation process is performed on the web server side and an attempt to forge
  them on the client side is pointless (for example, trying to unlock locked fields
  on a form, or trying to fill in invalid data).
- The data of the GET/POST request is checked for compliance with RFC standards and an attempt to
  call a URL with invalid characters will be processed correctly.
- The data of the GET/POST request is checked for the correspondence of the dimensions
  of the arrays and their indices.
- URL query arguments (http://domain/path?QUERY) are filtered.
- The basic module CAPTCHA minimizes the possibility of brute-force email address and password
  in the authentication form "form_login", brute-force email and/or username in the
  new user registration form "form_registration", brute-force email address in the access
  recovery form "form_recovery" and registration of spam robots in the new user registration
  form "form_registration".

Organizational vector:

- The system of permissions and roles provides the issuance of extended rights
  only to authorized persons.
- Only administrators have access to the profiles of other users (for example,
  users with the "Administrators" role).
- All email addresses of users are hidden from other system participants and it is
  not possible to calculate the correspondence between the user's name and his
  email address. Thus, it will be impossible to remotely reset the password of
  another user or try to guess the password for his account.
- It is possible to make changes to the user's registration data only if the
  password from his account is known — this minimizes the intra-system
  threat (security threat from personnel side).


Architecture
---------------------------------------------------------------------

The architecture is made according to the classic MVC scheme.
It is a hybrid system based on NoSQL and SQL storages.
NoSQL storage has a unique implementation and is a hybrid of document-oriented,
object-oriented and hierarchical models.

The system code is adapted for reuse.
The system consists of many small classes/class-patterns, which contain on average
from 3 to 15 methods which, in turn, consist of 3-15 lines of code. The perception
of the code is greatly facilitated by the "matrix" layout style (in some places resembles
the syntax of Python). Anything that seemed difficult was rejected or redone. Each
function was iteratively improved from 3 to 10 times. Functional testing was performed
on the entire set of combinatorial permutations.

The system includes a page with a UML diagram of all classes
and a link to download a JSON file with class descriptions in the
StarUML program format.


File organization
---------------------------------------------------------------------

The correct location of files in the system allows you to determine their purpose without
resorting to documentation.

The directories like "module_*/frontend" contain everything you need for frontend development.
The directories like "module_*/backend" contain everything you need for backend development.
The directories like "module_*/data" contain NoSQL-data.

In fact, the work of files does not depend on their location and if necessary,
they will still be found and processed. Location of files in specific directories — it is
only an organizational measure designed to facilitate the work with the system.

Additional modules/libraries should be placed in the "modules" directory so that
after updating the system via the administrator interface they are not cleared by
the Git system.

The system has a built-in parser and class loader PSR-0.
To add a new library (set of classes), just put its files on the
web server and reset the cache, after that they become available
from anywhere in the system.


Core: NoSQL
---------------------------------------------------------------------

All data is stored as PHP code.
Perhaps the fastest storage after "storage in RAM".
After organizing the disk in RAM, it is possible to increase performance
by 3-5 times (more details in the section "Performance improvement").
Each storage subdirectory will be initialized only on demand.

Any instance of the class and other NoSQL data can be described
in text format in a file of type ".data", like YAML, but has a more
stringent rules such as "each string can contain the only one
phrase in the form "key: value".

It is comfortable for controlling changes in the code — any change
of one key or value will be highlighted in "git diff" with just one line.
Also, this format significantly speeds up parsing the files.

Below is given an example of "*.data" file.

    demo
      object_1|class_name
        property_1: value 1
        property_2: value 2 …
        property_N: value N
      array_1
      - item_1: value 1
      - item_2: value 2 …
      - item_N: value N

At the same time, both objects (instances of class-patterns) and arrays
can have any nesting levels and contain inside any other objects or
arrays.

After parsing "*.data" files, the result is converted to PHP code (single
tree of objects — instances of class-patterns), after which it is
saved to files "dynamic/cache/cache-*.php" separately for each kind of
entity, as shown in the example below:
- dynamic/cache/data--blocks.php
- dynamic/cache/data--breadcrumbs.php
- dynamic/cache/data--file_types.php
and so on.

The example described above will be converted to a PHP
file of the following form:

    namespace effcore {
      cache::$data['demo'] = new \stdClass;
      cache::$data['demo']->object_1 = new class_name;
      cache::$data['demo']->object_1->property_1 = 'value 1';
      cache::$data['demo']->object_1->property_2 = 'value 2';
      cache::$data['demo']->object_1->property_N = 'value N';
      cache::$data['demo']->array_1['item_1'] = 'value 1';
      cache::$data['demo']->array_1['item_2'] = 'value 2';
      cache::$data['demo']->array_1['item_N'] = 'value N';
    }

This architecture give ability to access NoSQL data as quickly as possible.
When using the PHP module OPCache, the access speed can increase
from 2 to 3 times. In fact, to access NoSQL data, it is enough to
load a PHP file of a certain entity and data will be available
immediately after loading.

Thus, the core of the system is the aforementioned set of class-patterns
and NoSQL storage, which cache is represented as PHP code, containing
instances of these classes in tree form with any level of nesting
and unlimited by structure.

Changing the structure of NoSQL data is possible only through a special mechanism.
For example, the system menu is located in NoSQL storage and nobody cannot
disrupt its work. The anonymous user menu is stored in SQL storage and
the administrator can edit this menu through the system interface.

Field types are supported:
- integer;
- float;
- boolean;
- string;
- array;
- object|class_name;
- null.


Core: SQL
---------------------------------------------------------------------

MySQL and SQLite can be used as SQL storages.
The required versions can be found in the "readme/software.md" file.
Storage connection and data retrieval will initialize only on demand.
Denying access to SQL storage will not raise an error, but will only
make inaccessible part of the possibilities (for example, sessions
and login will be disconnected, and on the pages with election
"0 results" will be displayed).

The following are supported:
- checks;
- prepared queries (no chance for SQL-injections);
- transactions (begin, roll_back, commit);
- collations (nocase, binary);
- constraints (primary, unique, foreign with cascade action);
- index and unique index;
- connections to remote storages via manual initialization process;
- table prefixes.

Support for cascading foreign key actions:
- on update: "cascade" (not tested feature: "restrict", "no action");
- on delete: "cascade" (not tested feature: "restrict", "no action").

Cross-platform field types are supported:
- autoincrement;
- varchar;
- integer;
- real;
- time;
- date;
- datetime;
- boolean (as integer: 0|1);
- blob.

Other types allowed but not tested.
List of the tested types is sufficient for most tasks.
Only tested types are recommended for cross-platform compatibility reasons.
Distributed queries to remote storages not supported.

The main focus is on ANSI SQL.
PostgreSQL was excluded during development as Web RDBMS with
least compatible of ANSI standards.

It was decided not to use field of type timestamp.
Instead, it is recommended to use field of type datetime.
This field has a wide range of acceptable values (from "0001-01-01"
to "9999-12-31"), and also does not depend on the time zone.
When adding data to the server, dates should be converted to time zone UTC±0:00.
Instead of the original timestamp type, it is recommended
to use the integer type.


Core: scheme
---------------------------------------------------------------------


    ┌────────────────── classes ──────────────────┐             ┌────────────── noSQL data ──────────────┐
    │                                             │             │                                        │
    │  ╔═══════════════════════════════════════╗  │             │   ╔════════════════════════════════╗   │
    │  ║ /module_X/backend/pattern-class_1.php ║  │             │   ║ /module_X/data/instance_1.data ║   │
    │  ╠═══════════════════════════════════════╣  │             │   ╠════════════════════════════════╣   │
    │  ║ /module_X/backend/pattern-class_2.php ║  │             │   ║ /module_X/data/instance_2.data ║   │
    │  ╚═══════════════════════════════════════╝  │             │   ╚════════════════════════════════╝   │
    │                      …                      │             │                    …                   │
    │  ╔═══════════════════════════════════════╗  │    ┌───┐    │   ╔════════════════════════════════╗   │
    │  ║ /module_X/backend/pattern-class_N.php ║──────▶│ + │◀───────║ /module_X/data/instance_N.data ║   │
    │  ╚═══════════════════════════════════════╝  │    └───┘    │   ╚════════════════════════════════╝   │
    │                                             │      │      │                                        │
    └─────────────────────────────────────────────┘      │      └────────────────────────────────────────┘
                                                         │
                                                         │
           ╔══════ big tree (memory) ═════╗              │
           ║                              ║              │
           ║  data[class_instance_1] = {  ║              │
           ║    property_1: value_1       ║              │
           ║    property_2: value_2 …     ║              │
           ║    property_N: value_N }     ║              │
           ║                              ║              │
           ║  data[class_instance_2] = {  ║              │
           ║    property_1: value_1       ║              │
           ║    property_2: value_2 …     ║◀─────────────┘
           ║    property_N: value_N }     ║
           ║  …                           ║
           ║  data[class_instance_N] = {  ║
           ║    property_1: value_1       ║
           ║    property_2: value_2 …     ║
           ║    property_N: value_N }     ║
           ║                              ║
           ╚══════════════════════════════╝
                           │
                           ▼
    ╔════════════ /dynamic/cache/*.php ═══════════╗
    ║                                             ║
    ║   cache[instance_1] = new class             ║
    ║   cache[instance_1]->property_1 = value_1   ║
    ║   cache[instance_1]->property_2 = value_2 … ║
    ║   cache[instance_1]->property_N = value_N   ║
    ║                                             ║
    ║   cache[instance_2] = new class             ║
    ║   cache[instance_2]->property_1 = value_1   ║
    ║   cache[instance_2]->property_2 = value_2 … ║
    ║   cache[instance_2]->property_N = value_N   ║
    ║   …                                         ║
    ║   cache[instance_N] = new class             ║
    ║   cache[instance_N]->property_1 = value_1   ║
    ║   cache[instance_N]->property_2 = value_2 … ║
    ║   cache[instance_N]->property_N = value_N   ║
    ║                                             ║
    ╚═════════════════════════════════════════════╝


CSS, JS, SASS, LESS
---------------------------------------------------------------------

A single entry point allows the system to control the process of issuing
the contents of any file, making it possible to use external variables
in CSS and JS files. Such variables begin with "%%_", and the files
containing them have extensions "cssd" and "jsd".

The project does not implement and considers the CSS and JS streaming
compression technology to be obsolete.
It should be noted that the developers of "Google PageSpeed Insights" think differently.
Disadvantages of streaming compression:
- 1 to 10 KiB text files are compressed, that with modern data
  transfer rate is not significant, at the same time, compression
  takes web server resources, and for unpacking — the client (which
  ultimately affects the battery charge of mobile clients).
- for CSS, a file size above 10 KiB is considered to be the result
  of improper cascading of styles and/or usage of disastrous
  technology such as SASS or LESS, which condoning "badcoding".
- JS files can be initially compressed and obfuscated by
  reducing-reorganization of JS code by third-party
  programs or services.
However, no one bothers to use the streaming compression capabilities
of the web server directly.


Event model
---------------------------------------------------------------------

The event model is built fairly transparent and predictable.
It is enough to register a new event in the "events.data" of own module,
specify its weight and handler in PHP code, flush the cache and
the event will begin to be processed. On the "NoSQL Data → Events" page,
can view all events registered in the system (this section will be
available after enable the module "Develop").


Web server
---------------------------------------------------------------------

As a web server, Apache, NGINX, IIS are supported.
The required versions can be found in the "readme/software.md" file.


Caching
---------------------------------------------------------------------

Due to its architecture, mid-level projects do not require caching.
For large projects, caching is usually done by third-party web server tools,
what is originally supposed by the author.


Decoration
---------------------------------------------------------------------

To decorate the site/portal in the system exists the section "View".
In this section, the administrator can change the color scheme of the page elements.
The number of colors is limited by the built-in palette.
Additional colors can be added by creating a new profile.
Also in this section there are a color presets and "in one click" the
administrator can change the design of all available elements (for example,
activate the light design theme). There is also a list of the layouts which
available in the system.

The system operates with only one type of file collection — these are modules.
There are no a themes which familiar to many people.
To create a profile of decoration, see the "Deployment" section.


Deployment
---------------------------------------------------------------------

Making changes in system files is a bad idea, because all of them
will be lost after updating the system.

A good solution is to create a new module in the "modules" directory
with a profile of your own settings. An example of such a module can be borrowed
from the "Profiles" section. (just copy one of the profiles and place it
in the "modules" directory, then make your changes and reset the cache).

In this module you can set your own:
- colors;
- color presets;
- static cascading style files "*.css";
- static JavaScript files "*.js";
- dynamic cascading style files "*.cssd";
- dynamic JavaScript files "*.jsd";
- templates;
- layouts;
- pictures;
- favicons;
and more.

Also in the own module, it will be possible to apply the "Changes" mechanism.
This mechanism give ability to make changes in the system.
Its example is shown in the file "demo--data--changes.data" of the module "Demo".
The "Changes" mechanism modifies the global NoSQL tree, which, after clearing
the cache, will be converted by the system into PHP code.

After creating own module with the necessary settings the deployment process
will look extremely simple — just install the system and enable this module.


Localization
---------------------------------------------------------------------

The system already has translations of its interface into
Belarusian and Russian languages.
It is assumed that for each language version of the site/portal,
the administrator organizes his own subdomain.
In many cases, different language versions of a site/portal differ
not only in content, but also in a structure. For example, the main menu in one
language version may have one set of menu items, and on another — completely different,
in this case, the names of the menu items and their addresses and count will differ.
That is why it was decided not to complicate the system and not to introduce
in many aspects useless functionality.

If any page has a language different from the whole site, then this language
can be specified when creating/editing such a page.
In this case, the administrator should control the language of the content
that appears on this page.

The system uses the more perfect "Plural" system.
Using regular expressions, it is possible to describe almost any
dependence of a part of a word on the numeric and non-numeric arguments
which present in the phrase.


Performance improvement
---------------------------------------------------------------------

It is recommended to enable the PHP OPCache.
It is recommended to use the SSD (Solid State Drives).
In many cases, the "dynamic/cache" directory can be organized in RAM.
To increase the reliability level of the server, such RAM should
support ECC (error-correcting code), and the server itself should
work together with an UPS (uninterruptible power supply).


Licensing
---------------------------------------------------------------------

The system is open and free.
The system is not in the public domain.
Anyone can create a website, portal or service on the basis of it,
both personally and for any customer.
However, not allowed to distribute system files in their original or
modified form or in conjunction with anything else.
This restriction does not apply to third-party modules
whose authors themselves determine the licensing policy.

