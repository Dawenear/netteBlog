<?php

namespace App\Presenters;

use Nette;
use App\Model;



class WebPresenter extends BasePresenter {
    public function renderDefault(){
/*        $currentMonth = 3;
        $czechNames = [1 => 'leden', 'únor', 'březen', 'duben', 'květen', 'červen', 'červenec', 'srpen', 'září', 'říjen', 'listopad', 'prosinec'];
        for($i=1; $i<=12; $i++){
            if(($currentMonth + $i) > 12){
                $months[] = $czechNames[$currentMonth + $i - 12];
            }
            else{
                $months[] = $czechNames[$currentMonth + $i];
            }
        }
        $month = implode($months, ', ');
        $this->template->months = $month;
*/
    }
}
