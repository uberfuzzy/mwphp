mwphp
=====

a collection of php functions that wrap around the MediaWiki API and provide easy to use logical interfaces to sometimes complex logic, curl transports, and return codes.

History
=======

Much of the lowest level of this code was written in the pre-Wikia era, when I needed simple automated interaction with a remote MediaWiki that ran eq2i.com. These were designed to work on 1 wiki, so they have a very "single host" aspect

The mid level functions were written when I was a "Helper" (read: intern) for Wikia, and again needed automated tasks, but now mass actions on a single domain AND simple tasks across a list of domains.

The late state functions (the very finely niche functions) were written in the Wikia Staff (read:paid employee) era, when I needed to do complex mass tasks across many domains. Many of these are more finely refined, polished, error tolarent and well documented (the early stuff wasnt so much)

An overall goal of trying to keep this working with *any* MediaWiki install was attempted, but as this code was mainly used against Wikia servers, and their *unique*  tweaks, I eventually made consessions about how things worked. Most of these should be marked in the code. They were usually involved in login and edit functions, if I remember correctly.

Modularized
===========

The goal of this collection, was to provide a semi-modularized set of code that let you build quick bots/scripts to do things. You would only need to load/include the functions that you were using. Much of the heavy logic was still written as needed per script, but the interaction with MediaWiki was hidden and simplified.

An example of this would be deleting a list of pages in a category. A easy call to iterate over the list of page in a category, a little loop logic, and a call to the delete function. All the complex logic of parsing category structure arrays, paging over long sets of data, and handing the multitide of failure responces from the delete code were hidden from you. All the while doing this task, you never loaded the other 2 dozen functions for doing things like moving pages, or uploading images, only what you needed, and nothing else.

Think of this collection as a set of Legos, that let you just snap together chunks of pre-made code (with a little logic glue) to help rapidly develop a bot to do a task.

When building a house, you do not have to stop and design and invent a new drill. You just pick up the drill, and make the hole, and move on with your project :)

Release
=======
The steps leading to this release were sort of complex. Because some was written before Wikia, and some while under contract, there was some cloudy wording in my non-programmer contract that made it unclear if any of this that I wrote "to do my job" was considered "work for hire" and thus was under Wikia's control. Months after I left Wikia, I talked to some people, and decided that this was not the case, and I would be free to do with the code as I pleased.

That being said, there was also always a hesitation about releasing this very powerfull set of building blocks, because as much as it enabled me to do mass "work" very quickly, it was a double edged sword, and would also let "wiki vandals" and other such malcontents do "mass evil" as quickly (and in a nearly unstopable way). An example of this would think of a device that could let you walk through solid walls. Though could let firefighters rescue people very quickly and safely, but it could also let robbers steal as easily.


Warrenty, support, and the future
=

I make no claim of warrenty or support for this code. I no longer work for Wikia, nor deal with MediaWiki on a regular basis, so dont really have a place to test this against very quickly. I also dont know how much of this will still work with current versions of MediaWiki. Its been many months since I used any of this code, and was many before that, that I wrote any new function, and I know both MediaWiki and Wikia have upgraded since. Good luck!

I release this under some sort of open source license. I really dont know which. If you know of one that would be most compatible, let me know! Feel free to fork and continue etc, just maybe some sort of legacy credit would be nice, but required. Oh, and try not to do evil with it. :)