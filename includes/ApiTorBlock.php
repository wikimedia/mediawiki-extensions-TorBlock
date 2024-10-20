<?php

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

namespace MediaWiki\Extension\TorBlock;

use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiResult;
use Wikimedia\IPUtils;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * API module to check if an IP is blocked by Tor
 *
 * @ingroup API
 * @ingroup Extensions
 */
class ApiTorBlock extends ApiBase {

	public function execute() {
		$params = $this->extractRequestParams();

		if ( IPUtils::isIPAddress( $params['ip'] ) ) {
			$result = [
				ApiResult::META_BC_BOOLS => [ 'result' ],
				'result' => TorExitNodes::isExitNode( $params['ip'] ),
			];

			$this->getResult()->addValue( null, $this->getModuleName(), $result );
		} else {
			$this->dieWithError(
				[ 'apierror-torblock-badip', wfEscapeWikiText( $params['ip'] ) ],
				'badvalue'
			);
		}
	}

	/** @inheritDoc */
	public function getAllowedParams() {
		return [
			'ip' => [
				ParamValidator::PARAM_REQUIRED => true,
			],
		];
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 * @return array
	 */
	protected function getExamplesMessages() {
		return [
			'action=torblock&ip=192.0.2.18'
				=> 'apihelp-torblock-example-1',
		];
	}
}
