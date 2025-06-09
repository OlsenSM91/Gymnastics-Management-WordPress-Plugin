# WP Studio Manager

The WP Studio Manager plugin provides flexible management for sessions, participants, instructors and families across many industries.

---
This repository uses an organized file structure with separate directories for core functionality, admin screens, and assets.  A simple autoloader in the `core` folder loads classes automatically.
## Features

- **Class Management**
  - Create and manage sessions
  - Assign participants to sessions
  - Display schedules
  - Track available and remaining seats
  - Remove participants from sessions

- **Participant Management**
  - Add and manage families and their participants
  - Assign participants to sessions
  - View participant details

- **Instructor Management**
  - Add and manage instructors

---

## Installation

1. Upload the plugin files to the `/wp-content/plugins/wp-studio-manager` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to the WP Studio Manager section in the WordPress admin menu to begin managing sessions, participants, and instructors.

---

## Usage

### Class Management

![class-management1](https://github.com/OlsenSM91/Gymnastics-Management-WordPress-Plugin/assets/130707762/4f1fddc3-4ca7-4642-9ed0-bcb7e03e5ee5)
![class-management2](https://github.com/OlsenSM91/Gymnastics-Management-WordPress-Plugin/assets/130707762/b92dbf8a-f4ab-45e3-86ac-2c18e57f4f1d)

1. Navigate to **Studio Management > Sessions** to manage sessions.
2. Select a level to view and manage sessions under that level.
3. Add new sessions, assign participants, and view schedules.

### Participant Management

![athlete-management](https://github.com/OlsenSM91/Gymnastics-Management-WordPress-Plugin/assets/130707762/5c08c226-1bd4-4746-8260-5ba88cf7a7d4)

1. Navigate to **Studio Management > Families** to manage families and their participants.
2. Add new families and their participants.
3. Assign participants to sessions.

### Instructor Management

![coach-management](https://github.com/OlsenSM91/Gymnastics-Management-WordPress-Plugin/assets/130707762/f0035c95-d37c-42b7-b104-8f0d091e8401)

1. Navigate to **Studio Management > Instructors** to manage instructors.
2. Add new instructors and view instructor details.

---

## TODO

1. **classes.php**
   - Fix bug when assinging athlete/coach refreshes to main page instead of the existing class
   - Continue bug testing to ensure stability.

2. **coaches.php**
   - Further styling/UI details
   - Integrate as WordPress user "Coaches Role" which allows access to Studio Management, but that's it

3. **parents.php**
   - Although it's still called `parents.php` it has been rebranded as Participant Management
   - This page has proved difficult to work on. I finally have it functioning again so main reason for this commit update of early development
   - Functionality mostly complete, still need to add a Session view to it (previous fail was trying to have the ability to delete/assign session, may attempt again)

5. **Develop Parent Portal**
   - Begin development of a parent portal once the backend is complete.
   - Provide features for parents to view athlete schedules, class assignments, and other relevant information.
  
6. **Create Payment/Invoicing Features**
   - Integrate payment gateway such as stripe/woocommerce
   - Ability to invoice parent and collect payment online, in person or Zelle
   - Balance Tracked in Parent Profile and they're able to see it from their portal
     
8. **Create Agreements module**
   - Create agreements/waivers for parents to sign on their phone or computer
   - Add a true/false value to the parent database for any agreements completed
     
10. **Create Waiting List module**
    - When classes are out of slots, auto waiting list
    - Coach notification of waiting list

---

## Contributing

Contributions are welcome! Please open an issue or submit a pull request for any improvements or bug fixes.

---

## License

This plugin is not currently licensed but may be licensed in the future. This project is in early development
