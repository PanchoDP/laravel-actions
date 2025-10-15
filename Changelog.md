# Changelog

All notable changes to this project will be documented in this file.

## [2.0.1] - 2025-10-15

### 🎨 Styling

- Improve readme.md Banner


## [2.0.0] - 2025-10-05

### 🚀 Features

- [**breaking**] Change default behavior to instance methods and replace --i with --s flag
**BREAKING CHANGE:** Default method generation changed from static to instance methods.
**Changes:** - Replaced --i flag with --s flag for static method generation
- Default generated actions are now instance methods (non-static)
- Removed method_static configuration option from config file
- Updated all flag combinations to use 's' instead of 'i'
- Updated documentation and examples to reflect new behavior
- Updated all tests to match new default behavior

Migration guide for v1.x users:
1. Replace any usage of --i flag with --s flag in your scripts
2. Remove 'method_static' from config/laravel-actions.php if present
3. Be aware that new actions will be instance methods by default
4. Existing generated actions are not affected and will continue to work


## [1.7.0] - 2025-09-27

### 🚀 Features

- Add option for make method static or a instance


### 📚 Documentation

- Add information about new features and options


## [1.6.1] - 2025-09-09

### 🐛 Bug Fixes

- Fix test with the correct artisan command actions:list


## [1.6.0] - 2025-09-09

### 🚀 Features

- Add command to list the actions


### 📚 Documentation

- Update Readme with php artisan actions:list command information


## [1.5.1] - 2025-08-30

### ⚙️ Miscellaneous Tasks

- Add badge status in the package readme


### 🏗️ Build

- Add actions for test package in github


## [1.5.0] - 2025-08-30

### 🚀 Features

- Add request injection


## [1.4.3] - 2025-08-30

### 🐛 Bug Fixes

- Delete PhpDocs for params

- Delete use Throw


## [1.4.2] - 2025-08-29

### 📚 Documentation

- Improve readme.md file


## [1.4.1] - 2025-08-28

### 🚜 Refactor

- Refactor MakeActionCommand class


## [1.4.0] - 2025-08-28

### 🚀 Features

- Implement comprehensive security enhancements


## [1.3.0] - 2025-07-23

### 🚀 Features

- New configuration for method name


### 📚 Documentation

- Document the configuration file in readme


### ⚙️ Miscellaneous Tasks

- *(release)* Version 1.3.0


## [1.2.5] - 2025-07-17

### 📚 Documentation

- Add dev keyword

- Specify php versions


### ⚙️ Miscellaneous Tasks

- *(release)* Version 1.2.5


## [1.2.4] - 2025-07-15

### 🐛 Bug Fixes

- Add composer-require-dev: true


## [1.2.3] - 2025-07-14

### 📚 Documentation

- Update the readme file


### ⚙️ Miscellaneous Tasks

- Change to final classes

- *(release)* Prepare for v1.2.3


## [1.2.2] - 2025-07-13

### 🐛 Bug Fixes

- Minimum stability


### 💼 Other

- 1.2.2


## [1.2.1] - 2025-07-13

### 📚 Documentation

- Add default-require-dev in composer

- Update the Changelog to Version:1.2.1


## [1.2.0] - 2025-07-11

### 🚀 Features

- Add configuration file to change the principal default folder


### 💼 Other

- 1.2.0


### 📚 Documentation

- Add --dev to banner image

- Update the Readme.md adding publish config


### 🧪 Testing

- Add test for new features


## [1.1.2] - 2025-07-10

### 💼 Other

- 1.1.2


### 📚 Documentation

- Change banner image


## [1.1.1] - 2025-07-09

### 🐛 Bug Fixes

- Change minimum stability to dev


### 💼 Other

- 1.1.1


## [1.1.0] - 2025-07-07

### 🚀 Features

- Can make more than one subfolder


### 💼 Other

- 1.1.0


## [1.0.1] - 2025-07-06

### 💼 Other

- 1.0.1


### 📚 Documentation

- Add --dev to composer require in readme


## [1.0.0] - 2025-07-06

### 💼 Other

- 1.0.0


