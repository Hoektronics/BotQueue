BotQueue [![Build Status](https://travis-ci.org/Hoektronics/BotQueue.png?branch=0.5X-dev)](https://travis-ci.org/Hoektronics/BotQueue) [![Dependency Status](https://gemnasium.com/Hoektronics/BotQueue.svg)](https://gemnasium.com/Hoektronics/BotQueue)
========

Control your 3D Printer over the Internet.  Out of the box support for slicing, webcams, queue-based job management, temperature logging, API, and much more.  BotQueue works great for controlling a single 3D printer or dozens of printers.  Control your bot from anywhere in the world, even using your cellphone!  Currently supports RepRap style machines, but will expand to others if there is interest.

The main BotQueue site is at www.botqueue.com

Is this project dead?
-------------

Nope! I took a hiatus for a little while, but am back to working on this project. I'm working on the [laravel2](https://github.com/Hoektronics/BotQueue/tree/laravel2) branch. What I'll be doing most of the time is branching off of laravel2 and then submitting pull requests to make sure everything builds correctly. Once I'm satisfied with the full upgrade/redesign, I'll land the laravel2 branch onto master and deploy to the [main site](http://www.botqueue.com). If you're a registered user at that site, you'll get an email when the beta site goes live as well as when the main site is live with the new version. Look forward to it!

Philosophy and Goals
-------------

Digital fabrication / manufacturing is set to become one of the most important technologies of the 21st century.  Taking a cue from one of the most important technologies of the 20th century (the Internet) I believe that access to free and open tools is extremely important.  Allowing people of all skill levels access to these tools allows learning, innovation, and entrepreneurship to flower.  BotQueue aims to fulfill three goals:

1. Play Well With Others - interface with any digital fabrication device that will allow itself to be interfaced with.
1. Open and Free - all software behind BotQueue should be open source and freely available.  Both server and client side.
1. High Standards of Quality - lets build awesome software for everyone to do amazing things with!

Installation
-------------

Please visit http://www.botqueue.com/help for instructions on installing the software.

Contributing
-------------

1. Check for open issues or open a fresh issue on Github to start a discussion around a feature idea or a bug.
1. Fork the repository on Github to start making your changes.
1. Write a test which shows that the bug was fixed or that the feature works as expected.
1. Send a pull request and bug the maintainer until it gets merged and published. :) Make sure to add yourself to the contributors list below and the about page (web/views/main/about.php)

Contributors
-------------

We've received lots of help along the way, and here are some people who have contributed greatly to the BotQueue project directly or indirectly:

* Zach Hoeken Smith (project founder)
* Justin Nesselrotte (code+support)
* Joe Walnes (gcode display plugin)
* Tony Buser (stl viewer plugin)
* Alessandro Ranellucci (slic3r)

Of course this project also owes a huge debt to the many people who have developed awesome open source 3D printers, electronics, firmware, and all the other things that BotQueue is built on top of. Keep on rocking!
