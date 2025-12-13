<?php



namespace App\Services;



use \App\Models\Hierarchy as HierarchyModel;



class Hierarchy
{
    public function list_index ()
    {
        // (Setting the value)
        $results = [];

        foreach ( model( HierarchyModel::class )->list( [ 'id', 'name', 'description', 'color' ] ) as $hierarchy )
        {// Processing each entry
            // (Getting the value)
            $results[ $hierarchy->id ] = $hierarchy->values;
        }



        // Returning the value
        return $results;
    }
}



?>