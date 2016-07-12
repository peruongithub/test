<?php

namespace components;

use trident\Core;
use trident\Route;
use trident\Triad;

class defaultAppComponent extends Triad
{
    protected $actions = [
        'index',
    ];

    public function index()
    {
        return 'Hello word итд. и т.п.<br>'.((memory_get_usage() / 1024) / 1024).var_export(Route::all(), true);
    }

    public function index2()
    {
        //phpinfo(8);
        //cldrConverter::parsePluralRanges();
        //cldrConverter::parseLanguageData(CLDR_DIR);
        //cldrConverter::parseSupplementalData(CLDR_DIR);
        //cldrConverter::createEnum();

        /*
                $t=microtime(true);
                echo DateFormatter::formatDate(null,null,[
                        i18nEnum::OPT_LANGUAGE => Language::_AR,
                        i18nEnum::OPT_CALENDAR => Calendar::_GREGORIAN
                    ]).'<br>';
                echo DateFormatter::formatDateTimeInterval(
                        'Jan 10, 2008',
                        'Feb 12, 2008',
                        DateTimeIntervalSkeletons::_yMMMM,
                        [
                            i18nEnum::OPT_LANGUAGE => Language::_UK,
                            i18nEnum::OPT_CALENDAR => Calendar::_GREGORIAN
                        ]
                    ).'<br>';*/
        //echo DateFormatter::formatRelativeDateTimeFields(10,'year').'<br><hr>';
        //echo Formatter::formatList(['один','два','три','четыре']).'<br>';
        // echo DateFormatter::format("VVVV - vvvv - v - zzzz - z").'<br>';
        /*$opt = [
            //DateFormatter::OPK_LANGUAGE => Language::_RU,
            DateFormatter::OPK_REGION => Region::_AL
        ];
        $date = DateFormatter::getDate(DateFormatter::normalizeDatetimeValue('jul 12,2014'),$opt);
        $PP=['VVVV','vvvv','v','zzzz','z'];
        foreach($PP as$P){
            echo " '$P' ~ ".DateFormatter::formatTimeZone($P,$date,$opt).'<br>';
        }

        //echo DateFormatter::formatDateTime(null,DateFormatter::FORMAT_LONG,DateFormatter::FORMAT_LONG).'<br>';

        echo (microtime(true) - $t) .'<br><hr>';


        $t=microtime(true);
        $m = 'h {3,number,decimal,long}  g {3,number,currency,long} u {3,number,scientific} uy  f {0} gggg {0,number,unit,long} monkeys on {1,plural{one{hgh hgh};few{yui hukhk};many{yui many hukhk};other{yui {2,number} 45hukhk}}} trees make {2,number} monkeys per tree';

        echo Formatter::formatMessage($m,
            [
                [
                    'value' => '1421.0',
                    'options' => ['unitName' => 'energy-kilocalorie']
                ],
                123,
                '123456.7894465465',
                '-1420541678912345678.78951219',
                '2'
            ]
        );
        echo '<br>';
        echo (microtime(true) - $t) .'<br><hr>';
*/

//$this->gggh();
        /*$db = DI::get('mysqlDbDriver');

        $query = new A($db);

        echo '<pre>';
        echo $query->select('data')->where('id',$query::OP_GREATER_THEN_OR_EQUAL,2)->compileSelect();
        //$query->insert([['id'=>5,'data'=>'jhksh,glkh\'dgklhk']]);
        echo '</pre>';*/
        //echo $this->createUrl('sweraq/trewq', 'index', ['s' => 4546]). '<br>';

        /*$fs = new Filesystem(new LocalDriver(['root'=>'@root']));
        $ld1 = new LocalDriver(['root'=>'@trident']);
        $fs->mount('trident',$ld1);
        $fs->mount('trident/crypt/45/core',new LocalDriver(['root'=>'@trident/core']));
        */
        //print_r($fs->getDriverAndKey('trident/crypt/45/core/DI.php'));
        //print_r($fs->get('trident/crypt/45/core/DI.php'));
        //print_r($fs->listContents('trident/crypt',0));

//$log = new ChromePHPLogger();

        //$log->alert('hsdfgdhsgfdhsf');


//$this->response->setETag('1224154654645');
        //$this->response->status(206);

        //set_time_limit(0);
        /*
        $count = 0;$count3537 = 0;
        $current = 16;
        for($i = 1; $i <1001;$i++){
            $rand = (int)((rand(1,10)+rand(1,10))/2);
            $current =$current+ $rand;
            //echo $rand.'<br>';
            if($current>37){
                $current = 16;
            }elseif($current >= 35 && $current < 37){
                $count3537++;
            }
            elseif($current == 37){
                $count++;
            }

            //sleep(1);

        }
*/
        //$count3537.'<br>'.$count.'<br>'.
        return 'Hello word 222 итд. и т.п.<br>'; //. (microtime(true) - START_TIME) .
        // '<br>' . START_MEM_USAGE . '<br>' . ((memory_get_usage() / 1024) / 1024) . '<br>' .
        //((memory_get_peak_usage() / 1024) / 1024) . '<br>';
    }

} 
