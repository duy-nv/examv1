<?php

namespace App\Http\Controllers\API;

use App\Exceptions\GeneralException;
use App\Http\Requests\StoreChapterRequest;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateChapterRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Http\Resources\Subject\SubjectCollection;
use App\Models\Subject;
use App\Repositories\Subject\ChapterRepository;
use App\Repositories\Subject\SubjectRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SubjectController extends Controller
{
    public $subjectRepository;

    public $chapterRepository;

    /**
     * PHP 5 allows developers to declare constructor methods for classes.
     * Classes which have a constructor method call this method on each newly-created object,
     * so it is suitable for any initialization that the object may need before it is used.
     *
     * Note: Parent constructors are not called implicitly if the child class defines a constructor.
     * In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
     *
     * param [ mixed $args [, $... ]]
     * @link https://php.net/manual/en/language.oop5.decon.php
     */
    public function __construct(
      SubjectRepository $subjectRepository, ChapterRepository $chapterRepository){
        $this->subjectRepository = $subjectRepository;
        $this->chapterRepository = $chapterRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $conditions = [
            'orderBy' => ($request->order_by ? $request->order_by : 'name'),
            'sortDesc' => ($request->sort_desc == 'true' ? 'desc' : 'asc'),
            'perPage' => ($request->per_page && intval($request->per_page) > 0 ? $request->per_page: null)
        ];
        return new SubjectCollection($this->subjectRepository
            ->orderBy($conditions['orderBy'], $conditions['sortDesc'])
            ->paginate($conditions['perPage'])
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSubjectRequest $request)
    {
        return $this->subjectRepository->create($request->only(
            'code', 'name', 'credit', 'description'
        ));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSubjectRequest $request, $subject)
    {
        return $this->subjectRepository->updateById($subject->id, $request->only([
            'code', 'name', 'credit', 'description'
        ]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function storeChapter(StoreChapterRequest $request, $subjectId) {
      if($this->subjectRepository->existed($subjectId)) {
        $chapterData = $request->all() + ['subject_id' => (int)$subjectId];
        return $this->chapterRepository->create($chapterData);
      }
      throw new GeneralException(
        __('exceptions.invalid_data'),
        422
      );
    }

    public function updateChapter(UpdateChapterRequest $request, $subjectId, $chapterId) {
      if($this->chapterRepository->existed($chapterId)) {
        return $this->chapterRepository->updateById($chapterId, $request->all());
      }
      throw new GeneralException(
        __('exceptions.invalid_data'),
        422
      );
    }

    public function getChapters($subjectId) {
      return $this->subjectRepository->getChapters($subjectId);
    }

    public function storeQuestion(StoreQuestionRequest $request, $subjectId, $chapterId) {
      if($this->subjectRepository->containChapter($subjectId, $chapterId)) {
        return $this->subjectRepository->storeQuestion($request->all());
      }
      throw new GeneralException(
        __('exceptions.invalid_data'),
        422
      );
    }

    public function updateQuestion(UpdateQuestionRequest $request, $subjectId, $chapterId, $questionId) {
      if($this->chapterRepository->containQuestion($chapterId, $questionId)) {
        return $this->subjectRepository->updateQuestion($questionId, $request->all());
      }
      throw new GeneralException(
        __('exceptions.invalid_data'),
        422
      );
    }

    public function getQuestions(Request $request, $subjectId) {
      return $this->subjectRepository->getQuestions($subjectId, $request->all());
    }

}