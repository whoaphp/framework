<?php

/**
 * Copyright 2015-2019 info@neomerx.com
 * Modification Copyright 2021-2022 info@whoaphp.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Whoa\Contracts\FileSystem;

/**
 * @package Whoa\Contracts
 */
interface FileSystemInterface
{
    /**
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool;

    /**
     * @param string $filePath
     *
     * @return string
     */
    public function read(string $filePath): string;

    /**
     * @param string $filePath
     * @param string $contents
     *
     * @return void
     */
    public function write(string $filePath, string $contents): void;

    /**
     * @param string $filePath
     *
     * @return void
     */
    public function delete(string $filePath): void;

    /**
     * @param string $folderPath
     *
     * @return string[]
     */
    public function scanFolder(string $folderPath): array;

    /**
     * @param string $path
     *
     * @return bool
     */
    public function isFolder(string $path): bool;

    /**
     * @param string $path
     *
     * @return bool
     */
    public function isWritable(string $path): bool;

    /**
     * @param string $folderPath
     *
     * @return void
     */
    public function createFolder(string $folderPath): void;

    /**
     * @param string $folderPath
     *
     * @return void
     */
    public function deleteFolder(string $folderPath): void;

    /**
     * @param string $folderPath
     *
     * @return void
     */
    public function deleteFolderRecursive(string $folderPath): void;

    /**
     * @param string $targetPath
     * @param string $linkPath
     *
     * @return void
     */
    public function symlink(string $targetPath, string $linkPath): void;

    /**
     * @param string $path
     *
     * @return mixed
     */
    public function requireFile(string $path);
}
