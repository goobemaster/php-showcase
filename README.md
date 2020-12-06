# php-showcase
KISS solutions to blatantly easy tasks with the right ratio of server and client side code.

To try out a solution in your browser:
1. Clone this respository
2. Install PHP until `php -v` reports the version number ;) Or just run the included script called 'setup'
3. Make sure 'php-showcase' is executable, then run it in the terminal
4. Pick a solution
5. Open a standards compliant browser and load "http://localhost:8000/"

![Code Show Off Preview](images/setup-and-run.jpg)

Note: 'php-showcase' and 'setup' only supports Linux for the time being.

Pretty please: Open 'setup' and check what it actually does before you run it, because it may modify your system.

More solutions to come, depending on ideas/free time. Suggestions are welcome.

## 1) Code Show Off

![Code Show Off Preview](images/1-code-show-off.jpg)

Goal: Showing off your juicy code snippets on a page.

Requirements:
- Only one index page.
- Very fast page load.
- Basic page layout: logo, title, navigation bar to choose the snippet from, footer.
- Displaying one snippet at a time, chosen by the user from the navigation bar.
- Caching contents (code snippets) on client side.
- SEO friendly
- Responsive design (mobile friendly)

Restrictions (derived from requirements):
- No frameworks used
- No database used
- Vanilla PHP & JavaScript, standards compliant HTML5 & CSS3

Man-hours: Approximately 5 hours.
Total size: 45,6 kB (includes sample content)

To add a new snippet copy a source file to the "content" folder.

If you wish to allow a certain source file extension, open docroot/index.php and add a new line to the ALLOWED_CONTENT const, e.g.:

````
const ALLOWED_CONTENT = [
    'c' => 'Ansi C',
    'java' => 'Java',
    'py' => 'Python',
    'rb' => 'Ruby',
    'sh' => 'Shell Script' // << New stuff inserted here
];
````