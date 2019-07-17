import React, {Component} from "react";
import {Link} from "react-router-dom";
import User from "../Entity/Account/User";
import API from "../API/API";
import BaseObject from "../Serialization/BaseObject";
import FollowButton from "./FollowButton";
import VerifiedBadge from "./VerifiedBadge";
import Spin from "antd/es/spin";
import "antd/es/spin/style";

export default class SuggestedUsers extends Component<any, { loading: boolean, results: User[] }> {
	constructor(props) {
		super(props);

		this.state = {
			loading: true,
			results: []
		}
	}

	componentDidMount(): void {
		API.handleRequest("/suggestedUsers", "GET", {}, (data => {
			if (data["results"]) {
				const results: User[] = [];

				data["results"].forEach(userData => {
					results.push(BaseObject.convertObject(User, userData));
				});

				this.setState({results, loading: false});
			}
		}));
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.loading) {
			return <div className={"text-center my-3"}>
				<Spin size={"large"}/>
			</div>
		} else if (this.state.results.length > 0) {
			return <div className="card">
				<div className="card-header">
					Suggested
				</div>

				<div className="tab-content" id="users-tablist-content">
					{this.state.results.map((suggestedUser, i) => {
						return <div className="px-2 py-1 my-1" style={{height: "70px"}} key={i}>
							<Link to={"/" + suggestedUser.getUsername()} className="clearUnderline float-left">
								<img src={suggestedUser.getAvatarURL()} width="64" height="64" className="rounded"
									 alt={suggestedUser.getUsername()}/>
							</Link>

							<div className="ml-2 float-left">
								<Link to={"/" + suggestedUser.getUsername()} className="clearUnderline"
									  data-user-id="{{ suggestedUser.id }}">
									<div className="text-muted small float-left mt-1"
										 style={{
											 maxWidth: "160px",
											 overflow: "hidden",
											 textOverflow: "ellipsis",
											 whiteSpace: "nowrap",
											 wordWrap: "normal"
										 }}>
										@{suggestedUser.getUsername()}<VerifiedBadge target={suggestedUser}/>
									</div>
									<br/>
								</Link>

								<FollowButton target={suggestedUser} className={"mt-0 btn-sm"}/>
							</div>
						</div>
					})}
				</div>
			</div>;
		} else {
			return "";
		}
	}
}