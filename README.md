# Creamy
Creamy is a free, open source CRM for managing contacts & customers, lightweight, easy to use and customisable. Creamy is a full-fledged CRM framework that allows you to handle your contacts, customers and clients easily, manage the tasks associated with your business, and be notified of important events.

Creamy is right now under heavy development, and it's still in beta, so use at your own risk. Did you find a bug? Please let me know. It's probably full of them, so the sooner you notify me, the sooner I will try to fix them ;).

![](http://creamycrm.com/img/responsive.jpg)

## Users and Permissions
Creamy is a multi-user CRM. You can have as many users as you need. In order to control what data can be accessed by who, and who can delete, edit or modify the information stored in the system, Creamy implements a role permission scheme, so every user belongs to a certain role category:

* **Administrators**: Administrators have full control over the system. They are the only ones which can access the admin area, manage the users or modify the settings of Creamy. They also have all the permissions and attributions of the manager role, so they can read, add, modify and delete customers, assign tasks to themselves and others, read the notifications, read, send and receive messages, and access the statistics, among other things.
* **Managers**: Managers are top non-admin users in Creamy. They can read, add, modify and delete customers, assign tasks to themselves and others, read the notifications, read, send and receive messages, and access the statistics, among other things.
* **Writers**: Writers have writing permissions, but limited to themselves. As they are not managers, they cannot assign tasks to other users, but they can manage, create, delete and modify customers and contacts, and have access to messages and notifications.
* **Readers**: Readers are those users who need to query some information from the CRM from time to time, but don't really bother updating the customers and contacts. They have read access to customers and clients, and can access the notifications and messaging system.
* **Guests**: Guests don't have any permission on the system. They cannot access any information apart from the main page. They don't have access to customers, contacts, messages or notifications.

## Topbar

The top bar will give you quick access to your messages, notifications and tasks. Each icon has a badge with a number of unread or unattended elements to help you get a quick overview of things that would require your attention.

* The **messages** icon shows you your unread messages, and clicking on it will show you them as a list. Select any of the messages to read it directly.
* The **notifications** icon shows you your notifications for today, and clicking on it will show you them as a list.
* The **tasks** icon shows you your unfinished tasks, and clicking on it will show you them in a list.
* The **user** icon at the right, close to your name, will open a menu where you will be able to access or modify your user data, change your password or logout.

## Sidebar

The sidebar at the left will get you access to the different sections of Creamy. Let's have a look at what you can find there:

### Home
The home screen is where we are right now. It's the main page of Creamy. Here you can find some statistics about the progress in the number of customers, contacts and clients, along with a quick access to the messaging system and this tutorial. Any module or plugin can also install some views here for you to see if they want to allow you to access some functionality or give you a quick overview of the data they handle.

### Contacts and Customers
These sections contains all the contacts, customers and clients registered in your CRM, grouped by their type. This is the heart of Creamy. If this is the first time you (or someone in your company) is using Creamy, these sections will be empty, so one of the very first things you should do in order to enjoy Creamy is go to these sections and start filling in customers and contacts to feed your database!

### Messages
In the messages section you can access a messaging system for the users of the CRM. This is an inner communication tool to give you a quick way of sending messages, questions and meeting appointments to other members of your company or business.

### Notifications
This section will give you a timeline with information about all the important events that happened today or occurred during the past week, warning you of anything that's worth your attention: new customers, calendar events, and other issues. You will be able to access more details about the notification with just a click.

### Tasks
This section helps you to manage your time by registering tasks for your pending activities, meetings and work. You can mark them as completed, edit them, or delete them altogether. If you have manager or admin permissions, you will be able to assign tasks to other users as well.

### Admin
The Admin menu is only available to user with administration permissions. In the Admin menu, you will be able to configure every aspect of Creamy, add, remove or modify users, setup modules and adjust the settings of your CRM.

# Contribute

Creamy is open source and it's still in early beta (really early beta). I know I am not the best or the cleanest PHP programmer out there, so any help, suggestion and correction is welcome. If you are interested in the project, please consider joining the team!

You don't need to be a developer. I am also looking to translate Creamy to more languages (currently it's in English and Spanish only), so if you want to collaborate just let me know.

# License

The MIT License (MIT)

Copyright (c) 2015 Ignacio Nieto Carvajal

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

