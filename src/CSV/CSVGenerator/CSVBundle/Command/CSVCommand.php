<?php

namespace CSV\CSVGenerator\CSVBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Exception\Exception;

class CSVCommand extends ContainerAwareCommand
{

    private $csv_array=array(array('Month', 'Mid Month Meeting Date' ,'End of Month Testing Date'));
    /* indicates the number of months generated in the CSV */
    private $total_months=6;
    /* set the starting date of the CSV */
    private $starting_date='';
    private $banned_meeting_days=array('Sunday','Saturday');
    private $banned_testing_days=array('Sunday','Saturday','Friday');

    public function configure()
    {
        $help = <<<EOL
<comment>Example usage:</comment>
  <info>php app/console csv:generate</info>
  <info>php app/console csv:generate --startDate=25/06/2013</info>
  <info>php app/console csv:generate --startDate=25/06/2013 --months=5</info>

<info>The command generates the CSV for a period of 6 months starting from the current date.\nYou can change any of these parameters by adding a parameter like <comment>startDate</comment> or <comment>months</comment> to the command.
Keep in mind that if the start day is not the first day of the month the report will start with the next month!</info>
EOL;
        $this
            ->setName('csv:generate')
            ->setDescription('Generates the csv')
            ->setHelp($help)
            ->addOption(
                'startDate',
                null,
                InputOption::VALUE_OPTIONAL,
                'What will be the start date?',
                date('d/m/Y')
            )
            ->addOption(
                'months',
                null,
                InputOption::VALUE_OPTIONAL,
                'For how many months will you generate the report?',
                6
            );
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* get the values for the start date and period */
        $this->setStartingDate($input->getOption('startDate'));
        $this->setTotalMonths($input->getOption('months'));

        $CSV=$this->buildCSV();
        if($CSV['success']){
            $message='<info>'.$CSV['message'].'</info>';
        }else{
            $message='<error>'.$CSV['message'].'</error>';
        }
        $output->writeln($message);
    }

    private function buildCSV()
    {
        $starting_date = $this->getStartingDate();

        /* if this is the first day of the month begin with this month else begin with the next one */
        if(date('d',$starting_date)==1){
            $start_month=$starting_date;
        }else{
            $start_month=strtotime('+1 month',$starting_date);
        }

        /* calculate the meeting and testing dates for each month */
        for($i=0;$i<$this->getTotalMonths();$i++){
            if($i==0){
                $month= $start_month;
            }else{
                $month=strtotime('+'.$i.' months',$start_month);
            }

            /* set the values that will go in the CSV for each month */
            $current_month=date('F',$month).' '.date('Y',$month);
            $meeting_date=$this->getMidMonthMeetingDate($month);
            $testing_date=$this->getTestingDate($month);

            $this->csv_array[]=array($current_month,$meeting_date,$testing_date);

            /* get the start and end dates for the csv file name */
            if($i==0){
                /* get the first day of first month */
                $begin_date=date('01.m.Y',$month);
            }
            if($i==($this->total_months-1)){
                /* get the last day of last month */
                $end_date=date('t.m.Y',$month);
            }

        }

        /* write the CSV file */
        $kernel = $this->getContainer()->get('kernel');
        $csvDir=$kernel->getCSVDir();
        $csv_file_name=$begin_date.'-'.$end_date.'.csv';
        try{
            $fh=fopen($csvDir.$csv_file_name,'w');
            foreach ($this->csv_array as $fields) {
                fputcsv($fh,$fields);
            }
            fclose($fh);
            return array(
                'success'=>true,
                'message'=>$csv_file_name.' created in generatedCSV folder!'
            );
        }catch(Exception $e){
            return array(
                'success'=>false,
                'error'=>'Write error: '.$e
            );
        }
    }

    private function getMidMonthMeetingDate($month)
    {
        /* get the timestamp for the 14th day of the month */
        $meeting_day_timestamp=strtotime(date('Y-m-14',$month));

        /* verify if the day is Sunday or Saturday and move the date to next Monday if so */
        if(in_array(date('l',$meeting_day_timestamp),$this->banned_meeting_days)){
            $meeting_day_timestamp=strtotime('next Monday',$meeting_day_timestamp);
        }

        return date('d/m/Y',$meeting_day_timestamp);
    }

    private function getTestingDate($month)
    {
        /* get the timestamp for the last day of the month */
        $testing_day_timestamp=strtotime(date('Y-m-t',$month));

        /* verify if the day is Sunday or Saturday and move the date to next Monday if so */
        if(in_array(date('l',$testing_day_timestamp),$this->banned_testing_days)){
            $testing_day_timestamp=strtotime('previous Thursday',$testing_day_timestamp);
        }

        return date('d/m/Y',$testing_day_timestamp);
    }

    private function getStartingDate()
    {
        if($this->starting_date==''){
            return date('U');
        }else{
            return $this->starting_date;
        }
    }

    public function setStartingDate($string)
    {
        $date=explode('/',$string);
        if(is_array($date)&&count($date)==3){
            if(checkdate($date[1],$date[0],$date[2])){
                $this->starting_date=strtotime($date[2].'-'.$date[1].'-'.$date[0]);
            }
        }else{
            $this->starting_date=date('U');
        }
    }

    private function getTotalMonths()
    {
        if($this->total_months==0){
            return 6;
        }else{
            return $this->total_months;
        }
    }

    public function setTotalMonths($months)
    {
        if(is_int($months)&&$months>0){
            $this->total_months=$months;
        }else{
            $this->total_months=6;
        }
    }
}