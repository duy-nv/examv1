<?php


namespace App\Repositories\Term;


use App\Http\Resources\Term\TermResouce;
use App\Models\Term;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class TermRepository extends BaseRepository
{
    /**
     * Specify Model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return Term::class;
    }

    /**
     * Create a new model record in the database.
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $term =  parent::create(array_diff_key($data, array_flip(['subjects']))); // TODO: Change the autogenerated stub
            $term->subjects()->sync($data['subjects']);
            return new TermResouce($term);
        });
    }

    public function update(Term $term, array $data) {
        return DB::transaction(function() use ($term, $data){
            $term->update(array_diff_key($data, array_flip(['subjects'])));
            $term->subjects()->sync($data['subjects']);
            return new TermResouce($term);
        });
    }


}
