<?php
namespace X\Rekognition;
use \Aws\Rekognition\RekognitionClient;
use \Aws\Rekognition\Exception\RekognitionException;
use \X\Util\ImageHelper;
use \X\Util\Logger;

/**
 * Amazon Rekognition API client wrapper.
 *
 * Provides simplified interface for AWS Rekognition face detection and comparison.
 * Supports face collections, face matching, and face detection operations.
 *
 * Usage:
 * ```php
 * $client = new Client([
 *   'key' => 'AWS_ACCESS_KEY',
 *   'secret' => 'AWS_SECRET_KEY',
 *   'region' => 'ap-northeast-1'
 * ]);
 *
 * // Add face to collection
 * $faceId = $client->addFaceToCollection('my-collection', $imageDataUrl);
 *
 * // Search for similar faces
 * $match = $client->getFaceFromCollectionByImage('my-collection', $searchImage);
 * ```
 */
class Client {
  /**
   * RekognitionClient instance.
   * @var RekognitionClient
   */
  private $client;

  /**
   * Debug mode.
   * @var bool
   */
  private $debug;
 
  /**
   * Initialize Amazon Rekognition API client.
   *
   * @param array{
   *   region?: string,
   *   key: string,
   *   secret: string,
   *   connect_timeout?: int,
   *   debug?: bool
   * } $options Configuration options:
   *   - `region`: AWS region to connect to. Default is "ap-northeast-1".
   *   - `key`: **(required)** AWS access key ID.
   *   - `secret`: **(required)** AWS secret access key.
   *   - `connect_timeout`: Connection timeout in seconds. Default is 5.
   *   - `debug`: Output Rekognition responses to debug log. Default is false.
   * @throws \RuntimeException If key or secret is not provided.
   */
  public function __construct(array $options) {
    $options = array_merge([
      'region' => 'ap-northeast-1',
      'key' => null,
      'secret' => null,
      'connect_timeout' => 5,
      'debug' => false
    ], $options);
    if (empty($options['key']))
      throw new \RuntimeException('Amazon Rekognition access key is required');
    else if (empty($options['secret']))
      throw new \RuntimeException('Amazon Rekognition secret key is required');
    if ($options['debug'])
      Logger::debug('Options: ', $options);
    $this->client = new RekognitionClient([
      'region' => $options['region'],
      'version' => 'latest',
      'credentials' => [
        'key' => $options['key'],
        'secret' => $options['secret']
      ],
      'http' => [
        'connect_timeout' => $options['connect_timeout']
      ]
    ]);
    $this->debug = $options['debug'];
  }

  /**
   * Create a face collection.
   *
   * If the collection already exists, this method does nothing.
   *
   * @param string $collectionId Collection ID.
   * @return void
   * @throws \RuntimeException If creation fails for reasons other than duplicate.
   * @throws RekognitionException On AWS API error.
   */
  public function addCollection(string $collectionId): void {
    try {
      $res = $this->client->createCollection(['CollectionId' => $collectionId])->toArray();
      if ($this->debug)
        Logger::debug('Response: ', $res);
      $status = $res['StatusCode'] ?? null;
      if ($status != 200)
        throw new \RuntimeException('Collection could not be created');
    } catch (RekognitionException $e) {
      if ($e->getAwsErrorCode() !== 'ResourceAlreadyExistsException')
        throw $e;
    }
  }

  /**
   * Describe a face collection.
   *
   * @param string $collectionId Collection ID.
   * @return array|null Collection metadata, or null if not found.
   * @throws \RuntimeException If retrieval fails.
   * @throws RekognitionException On AWS API error.
   */
  public function getCollection(string $collectionId): ?array {
    try {
      $res = $this->client->describeCollection(['CollectionId' => $collectionId])->toArray();
      if ($this->debug)
        Logger::debug('Response: ', $res);
      $status = $res['@metadata']['statusCode'] ?? null;
      if ($status != 200)
        throw new \RuntimeException('Collection getting error');
      return $res;
    } catch (RekognitionException $e) {
      if ($e->getAwsErrorCode() !== 'ResourceNotFoundException')
        throw $e;
      return null;
    }
  }

  /**
   * List all face collection IDs.
   *
   * @return string[] Array of collection IDs.
   * @throws \RuntimeException If retrieval fails.
   */
  public function getAllCollections(): array {
    $res = $this->client->listCollections()->toArray();
    if ($this->debug)
      Logger::debug('Response: ', $res);
    $status = $res['@metadata']['statusCode'] ?? null;
    if ($status != 200)
      throw new \RuntimeException('Collection getting error');
    return !empty($res['CollectionIds']) ? $res['CollectionIds'] : [];
  }

  /**
   * Delete a face collection.
   *
   * If the collection does not exist, this method does nothing.
   *
   * @param string $collectionId Collection ID.
   * @return void
   * @throws \RuntimeException If deletion fails.
   * @throws RekognitionException On AWS API error.
   */
  public function deleteCollection(string $collectionId): void {
    try {
      $res = $this->client->deleteCollection(['CollectionId' => $collectionId])->toArray();
      if ($this->debug)
        Logger::debug('Response: ', $res);
      $status = $res['StatusCode'] ?? null;
      if ($status != 200)
        throw new \RuntimeException('Collection could not be delete');
    } catch (RekognitionException $e) {
      if ($e->getAwsErrorCode() !== 'ResourceNotFoundException')
        throw $e;
    }
  }

  /**
   * Check if a face collection exists.
   *
   * @param string $collectionId Collection ID.
   * @return bool True if the collection exists.
   */
  public function existsCollection(string $collectionId): bool {
    return !empty($this->getCollection($collectionId));
  }

  /**
   * Add a face to a collection.
   *
   * The image must contain exactly one face.
   *
   * @param string $collectionId Collection ID.
   * @param string $faceImage Face image as Data URL, binary blob, or file path.
   * @return string Unique face ID assigned by Rekognition.
   * @throws \RuntimeException If no face or multiple faces are detected, or indexing fails.
   */
  public function addFaceToCollection(string $collectionId, string $faceImage): string {
    if (ImageHelper::isDataURL($faceImage))
      // If the image is a data URL, convert it to a blob.
      $faceImage = ImageHelper::dataURL2Blob($faceImage);
    $faceCount = $this->getNumberOfFaces($faceImage);
    if ($faceCount === 0)
      throw new \RuntimeException('Face not detected');
    else if ($faceCount > 1)
      throw new \RuntimeException('Multiple faces can not be registered');
    $res = $this->client->indexFaces([
      'CollectionId' => $collectionId,
      'DetectionAttributes' => [ 'ALL' ],
      'Image' => [
        'Bytes' => $faceImage
      ]
    ])->toArray();
    if ($this->debug)
      Logger::debug('Response: ', $res);
    $status = $res['@metadata']['statusCode'] ?? null;
    if ($status != 200)
      throw new \RuntimeException('Collection face registration error');
    if (empty($res['FaceRecords']))
      throw new \RuntimeException('This image does not include faces');
    return $res['FaceRecords'][0]['Face']['FaceId'];
  }

  /**
   * List all faces in a collection.
   *
   * @param string $collectionId Collection ID.
   * @param int $maxResults Maximum number of faces to retrieve. Default is 4096.
   * @return array[] Array of face metadata from the collection.
   * @throws \RuntimeException If retrieval fails.
   */
  public function getAllFacesFromCollection(string $collectionId, int $maxResults=4096): array {
    $res = $this->client->listFaces(['CollectionId' => $collectionId, 'MaxResults' => $maxResults ])->toArray();
    if ($this->debug)
      Logger::debug('Response: ', $res);
    $status = $res['@metadata']['statusCode'] ?? null;
    if ($status != 200)
      throw new \RuntimeException('Collection face list acquisition error');
    return $res['Faces'];
  }

  /**
   * Search for the most similar face in a collection.
   *
   * Returns the single best match above the similarity threshold.
   *
   * @param string $collectionId Collection ID.
   * @param string $faceImage Face image as Data URL, binary blob, or file path.
   * @param int $threshold Minimum similarity percentage. Default is 80.
   * @return array{faceId: string, similarity: float}|null Best match, or null if no match found.
   */
  public function getFaceFromCollectionByImage(string $collectionId, string $faceImage, int $threshold=80): ?array {
    $maxFaces = 1;
    $detections = $this->getMultipleFacesFromCollectionByImage($collectionId, $faceImage, $threshold, $maxFaces);
    return !empty($detections) ? $detections[0]: null;
  }

  /**
   * Search for all similar faces in a collection.
   *
   * Returns all faces matching above the similarity threshold.
   *
   * @param string $collectionId Collection ID.
   * @param string $faceImage Face image as Data URL, binary blob, or file path.
   * @param int $threshold Minimum similarity percentage. Default is 80.
   * @param int $maxFaces Maximum number of matches to return. Default is 4096.
   * @return array{faceId: string, similarity: float}[] Array of matching faces with similarity scores.
   * @throws \RuntimeException If search fails.
   */
  public function getMultipleFacesFromCollectionByImage(string $collectionId, string $faceImage, int $threshold=80, int $maxFaces=4096): array {
    if (\preg_match('/^\//', $faceImage) && \is_file($faceImage))
      // If the image is a file path, read it as DataURL.
      $faceImage = ImageHelper::readAsBlob($faceImage);
    if (ImageHelper::isDataURL($faceImage))
      // If the image is a data URL, convert it to a blob.
      $faceImage = ImageHelper::dataURL2Blob($faceImage);
    $faceCount = $this->getNumberOfFaces($faceImage);
    if ($faceCount === 0)
      return [];
    $res = $this->client->searchFacesByImage([
      'CollectionId' => $collectionId,
      'FaceMatchThreshold' => $threshold,
      'Image' => [ 'Bytes' => $faceImage],
      'MaxFaces' => $maxFaces
    ])->toArray();
    if ($this->debug)
      Logger::debug('Response: ', $res);
    $status = $res['@metadata']['statusCode'] ?? null;
    if ($status != 200)
      throw new \RuntimeException('Collection getting error');
    if (empty($res['FaceMatches']))
      return [];
    $detections = array_map(function(array $faceMatche) {
      return [
        'faceId' => $faceMatche['Face']['FaceId'],
        'similarity' => round($faceMatche['Similarity'], 1)
      ];
    }, $res['FaceMatches']);
    return $detections;
  }

  /**
   * Check if a matching face exists in a collection.
   *
   * @param string $collectionId Collection ID.
   * @param string $faceImage Face image as Data URL, binary blob, or file path.
   * @param int $threshold Minimum similarity percentage. Default is 80.
   * @return bool True if a matching face is found.
   */
  public function existsFaceFromCollection(string $collectionId, string $faceImage, int $threshold=80): bool {
    return !empty($this->getFaceFromCollectionByImage($collectionId, $faceImage, $threshold));
  }

  /**
   * Delete faces from a collection.
   *
   * @param string $collectionId Collection ID.
   * @param string[] $faceIds Array of face IDs to delete.
   * @return void
   * @throws \RuntimeException If deletion fails.
   */
  public function deleteFaceFromCollection(string $collectionId, array $faceIds): void {
    $res = $this->client->deleteFaces([ 'CollectionId' => $collectionId, 'FaceIds' => $faceIds ])->toArray();
    if ($this->debug)
      Logger::debug('Response: ', $res);
    $status = $res['@metadata']['statusCode'] ?? null;
    if ($status != 200)
      throw new \RuntimeException('Collection face deletion error');
  }

  /**
   * Generate a unique collection ID.
   *
   * @return string Randomly generated collection ID.
   */
  public function generateCollectionId(): string {
    return uniqid(bin2hex(random_bytes(1)));
  }

  /**
   * Compare two face images for similarity.
   *
   * Returns 0.0 if no face is detected in either image.
   *
   * @param string $faceImage1 Source face image as Data URL, binary blob, or file path.
   * @param string $faceImage2 Target face image as Data URL, binary blob, or file path.
   * @return float Similarity percentage (0.0 to 100.0) between the two faces.
   * @throws \RuntimeException If comparison fails.
   */
  public function compareFaces(string $faceImage1, string $faceImage2): float {
    if (\preg_match('/^\//', $faceImage1) && \is_file($faceImage1))
      // If the image is a file path, read it as DataURL.
      $faceImage1 = ImageHelper::readAsBlob($faceImage1);
    if (\preg_match('/^\//', $faceImage2) && \is_file($faceImage2))
      // If the image is a file path, read it as DataURL.
      $faceImage2 = ImageHelper::readAsBlob($faceImage2);
    if ($this->getNumberOfFaces($faceImage1) === 0 || $this->getNumberOfFaces($faceImage2) === 0)
      // If no face is found in the image, the similarity rate returns zero.
      return .0;

    // Compare the faces in the two images.
    $res = $this->client->compareFaces([
      'SimilarityThreshold' => 0,
      'SourceImage' => [ 'Bytes' => ImageHelper::isDataURL($faceImage1) ? ImageHelper::dataURL2Blob($faceImage1) : $faceImage1 ],
      'TargetImage' => [ 'Bytes' => ImageHelper::isDataURL($faceImage2) ? ImageHelper::dataURL2Blob($faceImage2) : $faceImage2 ]
    ])->toArray();
    if ($this->debug)
      Logger::debug('Response: ', $res);
    $status = $res['@metadata']['statusCode'] ?? null;
    if ($status != 200)
      throw new \RuntimeException('Calculate similarit error');
    if (empty($res['FaceMatches']))
      return .0;
    return round($res['FaceMatches'][0]['Similarity'], 1);
  }

  /**
   * Detect faces in an image.
   *
   * @param string $faceImage Face image as Data URL, binary blob, or file path.
   * @param int $threshold Minimum confidence percentage for face detection. Default is 90.
   * @param 'DEFAULT'|'ALL' $attributes Detail level. "ALL" returns full facial analysis. Default is "DEFAULT".
   * @return array[] Array of detected face details above the confidence threshold.
   * @throws \RuntimeException If detection fails.
   */
  public function detectionFaces(string $faceImage, int $threshold=90, $attributes='DEFAULT'): array {
    // If the image is a file path, read it as DataURL.
    if (\preg_match('/^\//', $faceImage) && \is_file($faceImage))
      $faceImage = ImageHelper::readAsBlob($faceImage);
    $res = $this->client->DetectFaces([
      'Image' => [ 'Bytes' => ImageHelper::isDataURL($faceImage) ? ImageHelper::dataURL2Blob($faceImage) : $faceImage ],
      'Attributes' => [ $attributes ]])->toArray();
    if ($this->debug)
      Logger::debug('Response: ', $res);
    $status = $res['@metadata']['statusCode'] ?? null;
    if ($status != 200)
      throw new \RuntimeException('Face detection error');
    if (empty($res['FaceDetails']))
      return [];
    return array_filter($res['FaceDetails'], function(array $face) use ($threshold) {
      return $face['Confidence'] >= $threshold;
    });
  }

  /**
   * Count the number of faces detected in an image.
   *
   * @param string $faceImage Face image as Data URL, binary blob, or file path.
   * @param int $threshold Minimum confidence percentage for face detection. Default is 90.
   * @return int Number of faces detected above the confidence threshold.
   */
  public function getNumberOfFaces(string $faceImage, int $threshold=90): int {
    return count($this->detectionFaces($faceImage, $threshold));
  }
}