<h3 align="center">ougc Display Name</h3>

<div align="center">

[![Status](https://img.shields.io/badge/status-active-success.svg)]()
[![GitHub Issues](https://img.shields.io/github/issues/OUGC-Network/ougc-Display-Name.svg)](./issues)
[![GitHub Pull Requests](https://img.shields.io/github/issues-pr/OUGC-Network/ougc-Display-Name.svg)](./pulls)
[![License](https://img.shields.io/badge/license-GPL-blue)](/LICENSE)

</div>

---

<p align="center"> Allow users to use a display name to visually replace their unique username.
    <br> 
</p>

## 📜 Table of Contents <a name = "table_of_contents"></a>

- [About](#about)
- [Getting Started](#getting_started)
    - [Dependencies](#dependencies)
    - [File Structure](#file_structure)
    - [Install](#install)
    - [Update](#update)
    - [Template Modifications](#template_modifications)
- [Settings](#settings)
- [Templates](#templates)
- [Built Using](#built_using)
- [Authors](#authors)
- [Acknowledgments](#acknowledgement)
- [Support & Feedback](#support)

## 🚀 About <a name = "about"></a>

Allow users to use a display name to visually replace their unique username.

[Go up to Table of Contents](#table_of_contents)

## 📍 Getting Started <a name = "getting_started"></a>

The following information will assist you into getting a copy of this plugin up and running on your forum.

### Dependencies <a name = "dependencies"></a>

A setup that meets the following requirements is necessary to use this plugin.

- [MyBB](https://mybb.com/) >= 1.8
- PHP >= 7
- [MyBB-PluginLibrary](https://github.com/frostschutz/MyBB-PluginLibrary) >= 13

### File structure <a name = "file_structure"></a>

  ```
   .
   ├── inc
   │ ├── languages
   │ │ ├── english
   │ │ │ │ ├── admin
   │ │ │ │ │ ├── ougcDisplayName.lang.php
   │ │ │ ├── ougcDisplayName.lang.php
   │ ├── plugins
   │ │ │ ├── ougc
   │ │ │ │ │ ├── DisplayName
   │ │ │ │ │ │ ├── admin
   │ │ │ │ │ │ │ ├── config.php
   │ │ │ │ │ │ ├── hooks
   │ │ │ │ │ │ │ ├── admin.php
   │ │ │ │ │ │ │ ├── forum.php
   │ │ │ │ │ │ │ ├── shared.php
   │ │ │ │ │ │ ├── templates
   │ │ │ │ │ │ │ ├── controlPanelNavigation.html
   │ │ │ │ │ │ │ ├── controlPanelPage.html
   │ │ │ │ │ │ ├── settings.json
   │ │ │ │ │ │ ├── admin.php
   │ │ │ │ │ │ ├── core.php
   │ │ │ │ ├── ougcDisplayName.php
   ```

### Installing <a name = "install"></a>

Follow the next steps in order to install a copy of this plugin on your forum.

1. Download the latest package from the [MyBB Extend](https://community.mybb.com/mods.php) site or
   from the [repository releases](https://github.com/OUGC-Network/ougc-Display-Name/releases/latest).
2. Upload the contents of the _Upload_ folder to your MyBB root directory.
3. Browse to _Configuration » Plugins_ and install this plugin by clicking _Install & Activate_.
4. Browse to _Settings_ to manage the plugin settings.

### Updating <a name = "update"></a>

Follow the next steps in order to update your copy of this plugin.

1. Browse to _Configuration » Plugins_ and deactivate this plugin by clicking _Deactivate_.
2. Follow step 1 and 2 from the [Install](#install) section.
3. Browse to _Configuration » Plugins_ and activate this plugin by clicking _Activate_.
4. Browse to _Settings_ to manage the plugin settings.

### Template Modifications <a name = "template_modifications"></a>

To display the User Control Panel it is required that you edit the following template for each of your themes.

1. Place `<!--OUGC_DISPLAY_NAME-->` after `{$changenameop}` in the `usercp_nav_profile` template to display the user
   control panel navigation link.

[Go up to Table of Contents](#table_of_contents)

## 🛠 Settings <a name = "settings"></a>

Below you can find a description of the plugin settings.

### Main Settings

- **Minimum Display Name Length** `number`
    - _The minimum number of characters a display name can be._
- **Maximum Display Name Length** `number`
    - _The maximum number of characters a display name can be._
- **Clear Profile** `yesNo`
    - _Reset display names for users when clearing profiles._
- **Clearance Rules** `text`
    - _Line break separated list of regular expressions to match invalid usernames._
- **Page Action ** `text`
    - _Select the custom page action to use for this plugin._

[Go up to Table of Contents](#table_of_contents)

## 📐 Templates <a name = "templates"></a>

The following is a list of templates available for this plugin.

- `ougcDisplayName_controlPanelNavigation`
    - _front end_;
- `ougcDisplayName_controlPanelPage`
    - _front end_;

[Go up to Table of Contents](#table_of_contents)

## ⛏ Built Using <a name = "built_using"></a>

- [MyBB](https://mybb.com/) - Web Framework
- [MyBB PluginLibrary](https://github.com/frostschutz/MyBB-PluginLibrary) - A collection of useful functions for MyBB
- [PHP](https://www.php.net/) - Server Environment

[Go up to Table of Contents](#table_of_contents)

## ✍️ Authors <a name = "authors"></a>

- [@Omar G](https://github.com/Sama34) - Idea & Initial work

See also the list of [contributors](https://github.com/OUGC-Network/ougc-Display-Name/contributors) who participated in
this project.

[Go up to Table of Contents](#table_of_contents)

## 🎉 Acknowledgements <a name = "acknowledgement"></a>

- [The Documentation Compendium](https://github.com/kylelobo/The-Documentation-Compendium)

[Go up to Table of Contents](#table_of_contents)

## 🎈 Support & Feedback <a name="support"></a>

This is free development and any contribution is welcome. Get support or leave feedback at the
official [MyBB Community](https://community.mybb.com/thread-159249.html).

Thanks for downloading and using our plugins!

[Go up to Table of Contents](#table_of_contents)