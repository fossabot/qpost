/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpostapp.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
 */

import APIEndpoint from "./APIEndpoint";
import User from "../../Entity/Account/User";
import API from "../API";
import BaseObject from "../../Serialization/BaseObject";

export default class BirthdaysEndpoint extends APIEndpoint {
	private path: string = "/birthdays";

	/**
	 * Gets the users followed by the current user, that have their birthday in the coming weeks.
	 * @param date The date to base the calculation off of.
	 */
	public get(date: string): Promise<User[]> {
		return new Promise<User[]>((resolve, reject) => {
			return API.handleRequestWithPromise(this.path, "GET", {date}).then(value => {
				return resolve(BaseObject.convertArray(User, value));
			}).catch(reason => {
				return reject(reason);
			});
		});
	}
}