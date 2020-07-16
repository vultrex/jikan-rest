<?php

namespace App\Http\Controllers\V4DB;

use App\Anime;
use App\Http\QueryBuilder\SearchQueryBuilderProducer;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\MagazineCollection;
use App\Http\Resources\V4\ProducerCollection;
use App\Producer;
use Illuminate\Http\Request;
use Jikan\Request\Producer\ProducersRequest;

class ProducerController extends Controller
{

    private $request;
    const MAX_RESULTS_PER_PAGE = 25;

    /**
     *  @OA\Get(
     *     path="/producers",
     *     operationId="getProducers",
     *     tags={"producers"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Producers Resource",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function main(Request $request)
    {
        $page = $request->get('page') ?? 1;
        $limit = $request->get('limit') ?? self::MAX_RESULTS_PER_PAGE;

        if (!empty($limit)) {
            $limit = (int) $limit;

            if ($limit <= 0) {
                $limit = 1;
            }

            if ($limit > self::MAX_RESULTS_PER_PAGE) {
                $limit = self::MAX_RESULTS_PER_PAGE;
            }
        }

        $results = SearchQueryBuilderProducer::query(
            $request,
            Producer::query()
        );

        $results = $results
            ->paginate(
                $limit,
                ['*'],
                null,
                $page
            );

        return new ProducerCollection(
            $results
        );
    }

    /**
     *  @OA\Get(
     *     path="/producers/{id}",
     *     operationId="getProducerById",
     *     tags={"anime collection"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns Producer's anime",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function resource(Request $request, int $id)
    {
        $this->request = $request;
        $page = $this->request->get('page') ?? 1;

        $results = Anime::query()
            ->where('producers.mal_id', $id)
            ->orderBy('title');

        $results = $results
            ->paginate(
                self::MAX_RESULTS_PER_PAGE,
                ['*'],
                null,
                $page
            );

        return new AnimeCollection(
            $results
        );
    }
}
