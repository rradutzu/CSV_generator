CSV Generator

====================================

1.Installation

    - Clone or download the zip and extract the contents of the project inside the web directory of your web server
        e.g.:
        cd /var/www/
        git clone https://github.com/rradutzu/CSV_generator.git

    - Go inside the instalation folder and make sure you have permison to write in the generatedCSV, cache and logs folders
        e.g.:
        cd CSV_generator
        chmod -R 777 generatedCSV/
        chmod -R 777 app/cache/
        chmod -R 777 app/logs/

    Done!!!

2.Usage

    - Access the command from the command line
        e.g.:
        php app/console csv:generate
            * This will generate the CSV with the meeting and testing dates for the next 6 months starting with the today date.
              If the current day is the first day of the month, the CSV will include the current month also,
               if not the CSV entries will start with the next month.

    - Several options are available with the command, but the most important are: "startDate" and "months"
        * These options give user the possibility to change the start date for the CSV entries and respectively the
           number of months that the CSV will contain.
        * Keep in mind that no option is mandatory !!!
        e.g.:
        php app/console csv:generate --startDate=10/08/2013 --months=9

    - For a list of other options use the help option on the command
        e.g.:
        php app/console csv:generate --help

3.Conclusion

    Hope you like it! :)
