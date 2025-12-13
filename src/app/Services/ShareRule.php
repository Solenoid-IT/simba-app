<?php



namespace App\Services;



use \App\Models\ShareRule as ShareRuleModel;



class ShareRule
{
    public function list_index () : array
    {
        // (Setting the value)
        $results = [];

        foreach ( model( ShareRuleModel::class )->list( [ 'id', 'name' ] ) as $record )
        {// Processing each entry
            // (Getting the value)
            $results[ $record->id ] = element( $record );
        }



        // Returning the value
        return $results;
    }
}



?>