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

import Cookies from "js-cookie";
import User from "../Entity/Account/User";
import Header from "../Parts/Header";
import API from "../API/API";
import {message} from "antd";
import PushNotificationsManager from "../PushNotificationsManager";

export default class Auth {
	private static currentUser?: User;

	public static getCurrentUser(): User | undefined {
		return this.currentUser;
	}

	public static setCurrentUser(user?: User): void {
		this.currentUser = user;

		Header.update();
	}

	public static isLoggedIn(): boolean {
		return !!this.getToken();
	}

	public static getToken(): string | undefined {
		return Cookies.get("sesstoken");
	}

	public static setToken(token?: string) {
		if (token) {
			Cookies.set("sesstoken", token, {
				expires: 30
			});
		} else {
			Cookies.remove("sesstoken");
		}

		const ReactNativeWebView = window["ReactNativeWebView"];
		if (ReactNativeWebView) {
			ReactNativeWebView.postMessage({
				type: "token",
				token: Auth.getToken()
			});
		}
	}

	public static logout(noRedirect?: boolean, disableTokenDeletion?: boolean): void {
		if (typeof noRedirect === "undefined") noRedirect = false;

		if (disableTokenDeletion) {
			this.setToken(undefined);
			this.setCurrentUser(undefined);

			if (!noRedirect) {
				if (window["ReactNativeWebView"]) {
					window.location.href = "/login";
				} else {
					window.location.href = "/";
				}
			}
		} else {
			this.killPushSubscription(() => {
				if (this.getToken()) {
					API.token.delete(this.getToken()).then(() => {
						this.setToken(undefined);
						this.setCurrentUser(undefined);

						if (!noRedirect) {
							if (window["ReactNativeWebView"]) {
								window.location.href = "/login";
							} else {
								window.location.href = "/";
							}
						}
					}).catch(error => {
						message.error(error);
					});
				}
			});
		}
	}

	private static killPushSubscription(callback?) {
		const WebPushClient = PushNotificationsManager.WebPushClient;
		if (WebPushClient) {
			const subscription = WebPushClient.getSubscription();

			if (subscription) {
				subscription.unsubscribe().then(value => {
					if (callback) callback();
				}).catch(reason => {
					console.error("Failed to kill push subscription", reason);
					if (callback) callback();
				});
			} else {
				if (callback) callback();
			}
		} else {
			if (callback) callback();
		}
	}
}